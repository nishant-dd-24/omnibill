<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\IdentityAccess\Http\Controllers\AuthController;
use Modules\IdentityAccess\Http\Controllers\UserController;
use Modules\Subscription\Http\Controllers\AdminCatalogController;
use Modules\Subscription\Http\Controllers\CatalogController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);

    // Catalog
    Route::get('/plans', [CatalogController::class, 'index']);
    Route::post('/admin/plans', [AdminCatalogController::class, 'store']);
});
