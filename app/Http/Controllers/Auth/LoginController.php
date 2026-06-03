<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Module: Auth  |  Page: Login / Logout
 *
 * RESPONSIBILITIES
 * ----------------
 *  - Display the login form (guest-only).
 *  - Authenticate a user against the `users` table using email + password.
 *  - Honour the "Remember Me" checkbox so chosen users stay signed in for
 *    Laravel's default ~5-year remember-cookie window (re-validated against
 *    `users.remember_token`).
 *  - Cleanly log a user out: kill the session, rotate the CSRF token, and
 *    clear the remember cookie / token (handled internally by Auth::logout).
 *
 * SECURITY NOTES
 * --------------
 *  - On successful login we regenerate the session ID to defeat fixation.
 *  - On logout we both invalidate the session AND regenerate the CSRF token
 *    so the next page load gets a clean slate.
 *  - The actual "new login" email is dispatched by SendLoginNotificationEmail
 *    listening to the framework's Login event - this controller never
 *    needs to know about it.
 */
class LoginController extends Controller
{
    /**
     * GET /login - show the login form (only for guests).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * POST /login - validate credentials and start a session.
     *
     * The `remember` checkbox (truthy values: "1", "on", true) is forwarded to
     * Auth::attempt(), which is what tells Laravel to set the persistent
     * `remember_*` cookie. Without it, sessions die after SESSION_LIFETIME.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Defeat session fixation by issuing a fresh session ID
            // (the user's identity is preserved across the regeneration).
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * POST /logout - end the session and revoke remember-me persistence.
     *
     * Auth::logout() takes care of clearing the remember cookie and nulling
     * the user's `remember_token` column, so a stolen device that still has
     * the cookie cannot re-authenticate after this call.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
