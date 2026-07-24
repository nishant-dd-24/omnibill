<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\IdentityAccess\Http\Controllers\AuthController;
use Modules\IdentityAccess\Http\Controllers\UserController;
use Modules\Invoice\Http\Controllers\CreditNoteController;
use Modules\Invoice\Http\Controllers\InvoiceController;
use Modules\Shared\Http\Middleware\IdempotencyMiddleware;
use Modules\Subscription\Http\Controllers\AdminCatalogController;
use Modules\Subscription\Http\Controllers\CatalogController;
use Modules\Subscription\Http\Controllers\SubscriptionController;
use Modules\Webhook\Http\Controllers\StripeWebhookController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Webhooks
    Route::post('/webhooks/stripe', StripeWebhookController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('customers', CustomerController::class);

        // Catalog
        Route::get('/plans', [CatalogController::class, 'index']);
        Route::post('/admin/plans', [AdminCatalogController::class, 'store']);

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);

        // Credit Notes
        Route::get('/credit-notes', [CreditNoteController::class, 'index']);
        Route::get('/credit-notes/{creditNote}', [CreditNoteController::class, 'show']);

        // Subscriptions
        Route::middleware(IdempotencyMiddleware::class)->group(function () {
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::post('/invoices/{invoice}/finalize', [InvoiceController::class, 'finalize']);
            Route::post('/invoices/{invoice}/credit-notes', [CreditNoteController::class, 'store']);
        });
    });
});
