<?php

use Illuminate\Support\Facades\Route;
use Modules\Future\Http\Livewire\Index;
use Modules\Future\Http\Controllers\FutureController;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('future') // Use a clear, memorable URL prefix
    ->name('future.')
    ->group(function () {
        
        Route::get('/', Index::class)->name('index');
        
        // Route::get('/{parentId}', Index::class)
        //     ->name('folder')
        //     ->where('parentId', '[0-9]+'); 
    });
