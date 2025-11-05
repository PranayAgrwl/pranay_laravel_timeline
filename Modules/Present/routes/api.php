<?php

use Illuminate\Support\Facades\Route;
use Modules\Present\Http\Controllers\PresentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('presents', PresentController::class)->names('present');
});
