<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Module: Auth  |  Page: Profile (self-service)
 *
 * RESPONSIBILITIES
 * ----------------
 *  - Let the currently authenticated user view and update their own profile:
 *      * name
 *      * email
 *      * password (optional)
 *
 *  - Enforce the standard "credential confirmation" rule: ANY change requires
 *    the user to retype their CURRENT password. This blocks a stolen session
 *    or an unattended browser tab from being used to silently take over the
 *    account (someone with the cookie still can't change the email or
 *    password without knowing the existing password).
 *
 * VALIDATION RULES
 * ----------------
 *  - current_password: required; verified against the hashed value in DB.
 *  - name           : required, max 255.
 *  - email          : required, valid email, unique among OTHER users.
 *  - password       : optional; when present, must be confirmed AND meet
 *                     Laravel's default Password rule (>= 8 chars).
 *
 * SECURITY NOTES
 * --------------
 *  - On password change we DO NOT regenerate the session because the user is
 *    already on this device; forcing a re-login here would surprise them.
 *    We DO however log them out of all OTHER devices via
 *    Auth::logoutOtherDevices() so a stolen session elsewhere cannot persist.
 *    (This silently re-hashes the user's password key in their current
 *    session, no extra UX required.)
 */
class ProfileController extends Controller
{
    /**
     * GET /profile - render the edit form, prefilled with the user's data.
     */
    public function edit(Request $request)
    {
        return view('auth.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * PATCH /profile - validate input, gate on current_password, then persist.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'name'             => ['required', 'string', 'max:255'],
            'email'            => [
                'required', 'email', 'max:255',
                // unique:users,email, EXCEPT the current user's own row
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            // Optional new password: only enforced when the field is filled in
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);

            // Save first so the new hash is the one logoutOtherDevices checks against.
            $user->save();

            // Kill OTHER devices' sessions for this user; current session keeps working.
            // Requires AuthenticateSession middleware on the route OR uses the
            // session's stored password hash. Laravel handles the bookkeeping.
            Auth::logoutOtherDevices($validated['password']);
        } else {
            $user->save();
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}
