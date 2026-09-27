<?php

use App\Http\Controllers\Api\V1\PublicBylawController;
use App\Http\Controllers\Api\V1\PublicPolicyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/policies', [PublicPolicyController::class, 'index']);
    Route::get('/policies/{slug}', [PublicPolicyController::class, 'show']);

    Route::get('/bylaws', [PublicBylawController::class, 'index']);
    Route::get('/bylaws/{slug}', [PublicBylawController::class, 'show']);
});
