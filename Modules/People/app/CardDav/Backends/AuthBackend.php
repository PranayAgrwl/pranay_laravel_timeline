<?php

namespace Modules\People\CardDav\Backends;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Sabre\DAV\Auth\Backend\AbstractBasic;

/**
 * AuthBackend
 *
 * HTTP Basic authentication for the CardDAV endpoint, validated
 * against the application's `users` table.
 *
 * Why Basic auth (and not session-cookie):
 *   - DAVx5 (the Android CardDAV client) speaks HTTP Basic over HTTPS.
 *     That's the de-facto auth for CardDAV in 2025.
 *   - Caddy already terminates TLS, so the password is encrypted on
 *     the wire (the only place it appears in plain text is inside the
 *     local container memory during validation).
 *
 * The `username` accepted from the client is the user's email address
 * (matches your web-login flow). Sabre uses this string as the
 * principal id (so the principal URI becomes `principals/EMAIL`),
 * which is also what we feed back to the rest of the DAV tree.
 */
class AuthBackend extends AbstractBasic
{
    public function __construct(string $realm = 'Pranay Personal CRM (CardDAV)')
    {
        $this->realm = $realm;

        // sabre/dav uses this prefix to build the principal URI from
        // the validated username. Keep it identical to PrincipalBackend's
        // prefix or principal discovery will not find the user.
        $this->principalPrefix = 'principals/';
    }

    /**
     * Validate the incoming Basic-auth credentials.
     *
     * Returns true on success, false on failure. sabre/dav handles
     * the 401 response and retry semantics on top of us.
     */
    protected function validateUserPass($username, $password): bool
    {
        $user = User::query()->where('email', $username)->first();
        if ($user === null) {
            return false;
        }

        if (! Hash::check($password, $user->password)) {
            return false;
        }

        return true;
    }
}
