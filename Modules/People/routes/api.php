<?php

use Illuminate\Support\Facades\Route;
use Modules\People\Http\Controllers\PeopleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('people', PeopleController::class)->names('people');
});
