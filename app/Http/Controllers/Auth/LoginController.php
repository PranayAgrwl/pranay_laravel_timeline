<?php

namespace App\Http\Controllers\Auth;
// This tells PHP and Laravel exactly where this LoginController lives in your project.

use App\Http\Controllers\Controller;
// Imports the basic "Controller" blueprint of laravel
use Illuminate\Http\Request;
// Imports the "Request" tool. This object carries all the data that user has entered in a form to us
use Illuminate\Support\Facades\Auth;
// Imports the "Auth" security guard. This is the main tool Laravel uses to check passwords, log users in, and log them out.

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login (Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials))
        {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInputs('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
