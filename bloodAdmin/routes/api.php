<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HopitalAuthController;

// Routes publiques (sans auth)
Route::prefix('hopitals')->group(function () {
    Route::post('/auth/login', [HopitalAuthController::class, 'login']);
    Route::post('/auth/register', [HopitalAuthController::class, 'register']);
});

// Routes protégées (avec Sanctum)
Route::middleware('auth:sanctum')->prefix('hopitals')->group(function () {
    Route::get('/auth/verify', [HopitalAuthController::class, 'verify']);
    Route::post('/auth/logout', [HopitalAuthController::class, 'logout']);
    Route::get('/profile', [HopitalAuthController::class, 'getProfile']);
    Route::put('/profile', [HopitalAuthController::class, 'updateProfile']);
});