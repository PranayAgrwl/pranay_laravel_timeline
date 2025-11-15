<?php

use Illuminate\Support\Facades\Route;
use Modules\Future\Http\Controllers\FutureController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('futures', FutureController::class)->names('future');
});
