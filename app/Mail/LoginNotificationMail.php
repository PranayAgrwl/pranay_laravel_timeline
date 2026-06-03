<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Module: Auth  |  Mailable: New Login Notification
 *
 * Sent to a user every time their account is successfully authenticated -
 * including silent re-authentications via the "Remember Me" cookie. The
 * goal is twofold:
 *
 *   1. Reassurance for the user that the sign-in was them.
 *   2. Early-warning for the user when the sign-in WASN'T them, with a
 *      clear path to action (change the password).
 *
 * IMPLEMENTATION CHOICES
 * ----------------------
 *  - SYNCHRONOUS by design (NO ShouldQueue). The mail goes out inside the
 *    login request itself, so there are no moving parts to babysit - no
 *    queue worker to keep running, no jobs piling up in the `jobs` table.
 *    The trade-off is ~1-2s of extra login latency while we talk to SMTP.
 *    See SendLoginNotificationEmail listener: it wraps the dispatch in
 *    try/catch so a flaky mail server can NEVER break a login.
 *
 *  - Reply-To is pulled from MAIL_REPLY_TO_ADDRESS / MAIL_REPLY_TO_NAME so
 *    a user replying lands in your support inbox, not the no-reply sender.
 *
 *  - All "metadata" the email displays (IP, UA, time...) is captured at
 *    listener time and passed into the constructor. Even though we render
 *    synchronously today, keeping the constructor self-contained means
 *    re-introducing ShouldQueue later is a zero-friction one-line change.
 */
class LoginNotificationMail extends Mailable
{
    public function __construct(
        public User    $user,
        public string  $ipAddress,
        public string  $userAgentRaw,
        public string  $browser,
        public string  $os,
        public Carbon  $loginAt,
        public bool    $viaRememberCookie,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: 'New login to your ' . config('app.name') . ' account',
            to:      [new Address($this->user->email, $this->user->name)],
        );

        // Optional courtesy header: replies land in your support inbox.
        if ($replyTo = config('mail.from.reply_to') ?? env('MAIL_REPLY_TO_ADDRESS')) {
            $envelope = $envelope->replyTo([
                new Address($replyTo, env('MAIL_REPLY_TO_NAME', config('app.name'))),
            ]);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-notification',
        );
    }
}
