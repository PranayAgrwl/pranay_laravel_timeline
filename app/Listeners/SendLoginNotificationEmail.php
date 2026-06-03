<?php

namespace App\Listeners;

use App\Mail\LoginNotificationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Module: Auth  |  Listener: Login -> "new login" email
 *
 * Fires every time Laravel's Auth subsystem successfully authenticates a user
 * (fresh login, remember-cookie re-auth, programmatic Auth::login, etc.) and
 * dispatches a notification email to that user.
 *
 * REGISTRATION
 * ------------
 * Registered explicitly in AppServiceProvider::boot() rather than relying on
 * Laravel's auto-discovery so the wiring is greppable and obvious to future
 * readers of the codebase.
 *
 * NETWORK DATA
 * ------------
 * We snapshot the request's IP and User-Agent right here and pass them into
 * the Mailable constructor. Even though the mail is now sent synchronously
 * inside the login request, keeping the constructor self-contained means we
 * can re-introduce ShouldQueue later with zero further code changes.
 *
 * RESILIENCE
 * ----------
 *  1. If the user has no email address (shouldn't happen, but defensive) we
 *     silently skip - a missing email must never break a login.
 *  2. The Mail::send() call is wrapped in try/catch. Because the mailable is
 *     no longer queued, an SMTP outage would otherwise bubble up as a 500
 *     and lock the user out of their own app. Logging is the right failure
 *     mode here: the login succeeded, only the notification did not.
 */
class SendLoginNotificationEmail
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Defensive: only proceed for our own User model with a valid email.
        if (! $user instanceof User || empty($user->email)) {
            return;
        }

        $request   = request();
        $userAgent = (string) ($request?->userAgent() ?? 'unknown');
        $ip        = (string) ($request?->ip() ?? 'unknown');

        [$browser, $os] = $this->parseUserAgent($userAgent);

        try {
            Mail::send(new LoginNotificationMail(
                user:              $user,
                ipAddress:         $ip,
                userAgentRaw:      $userAgent,
                browser:           $browser,
                os:                $os,
                loginAt:           Carbon::now(),
                viaRememberCookie: (bool) ($event->remember ?? false),
            ));
        } catch (Throwable $e) {
            // SMTP failure must never block login. Log loudly for ops triage.
            Log::error('Login notification email failed to send', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $ip,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Very lightweight User-Agent parser. Returns [browser, os] strings.
     *
     * We deliberately AVOID pulling in a heavyweight UA-parsing package
     * (jenssegers/agent, ua-parser, etc.) because the failure mode of a
     * misparse here is "the email shows the OS as Unknown" - not worth a
     * new dependency. Cases we care about cover ~95% of real-world traffic.
     */
    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown browser';
        $os      = 'Unknown OS';

        // --- Browser detection (order matters: Edge/Brave/Opera before Chrome) ---
        if (stripos($ua, 'Edg/') !== false)            { $browser = 'Microsoft Edge'; }
        elseif (stripos($ua, 'OPR/') !== false
             || stripos($ua, 'Opera') !== false)        { $browser = 'Opera'; }
        elseif (stripos($ua, 'Brave/') !== false)       { $browser = 'Brave'; }
        elseif (stripos($ua, 'Firefox/') !== false)     { $browser = 'Firefox'; }
        elseif (stripos($ua, 'Chrome/') !== false)      { $browser = 'Chrome'; }
        elseif (stripos($ua, 'Safari/') !== false)      { $browser = 'Safari'; }

        // --- OS detection ---
        if (stripos($ua, 'Windows NT 10') !== false)    { $os = 'Windows 10/11'; }
        elseif (stripos($ua, 'Windows') !== false)      { $os = 'Windows'; }
        elseif (stripos($ua, 'Android') !== false)      { $os = 'Android'; }
        elseif (stripos($ua, 'iPhone') !== false
             || stripos($ua, 'iPad') !== false)         { $os = 'iOS'; }
        elseif (stripos($ua, 'Mac OS X') !== false)     { $os = 'macOS'; }
        elseif (stripos($ua, 'Linux') !== false)        { $os = 'Linux'; }

        return [$browser, $os];
    }
}
