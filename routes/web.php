<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;



Route::controller(LoginController::class)->group(function () {
    // 1. Show Form
    Route::get('/login', 'showLoginForm')->name('login')->middleware('guest');
    
    // 2. Process Login
    Route::post('/login', 'login');
    
    // 3. Process Logout
    Route::post('/logout', 'logout')->name('logout');
});



Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('layouts.app');
    })->name('home');


    Route::get('test', function () {
        return view('project.test');
    })->name('test');



});