<?php

use Illuminate\Support\Facades\Route;
use Modules\Present\Http\Controllers\PresentController;
use Modules\Present\Http\Controllers\PresentUnitsController;
use Modules\Present\Http\Controllers\PresentHabitsController;
use Modules\Present\Http\Controllers\PresentLogsController;
use Modules\Present\Http\Controllers\PresentReportsController;
// use Modules\Present\Livewire\Report\Index;

Route::middleware(['auth'])->prefix('present')->group(function () {
    Route::get('/', [PresentController::class, 'index'])->name('present.index'); 

    Route::resource('units', PresentUnitsController::class)->names('present.units');
    Route::resource('habits', PresentHabitsController::class)->names('present.habits');
    Route::resource('logs', PresentLogsController::class)->names('present.logs');
    Route::get('/report', [PresentReportsController::class, 'index'])->name('present.report.index');
    // Route::get('/report', Index::class)->name('present.report.index');
});
