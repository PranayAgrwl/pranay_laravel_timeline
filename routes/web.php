<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;

/*
|--------------------------------------------------------------------------
| Public / Guest routes
|--------------------------------------------------------------------------
| Login & logout. Logout is POST-only to prevent CSRF-via-GET attacks.
*/
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login')->middleware('guest');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});


/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('layouts.app');
    })->name('home');

    Route::get('test', function () {
        return view('project.test');
    })->name('test');

    // ----- Profile (self-service edit) ----------------------------------
    // PATCH is the semantically correct verb for a partial-update form.
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/',  'edit')->name('profile.edit');
        Route::patch('/', 'update')->name('profile.update');
    });
});
