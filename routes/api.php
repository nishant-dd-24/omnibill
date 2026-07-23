<?php

use Illuminate\Support\Facades\Route;
use Modules\IdentityAccess\Http\Controllers\AuthController;
use Modules\IdentityAccess\Http\Controllers\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
});
