<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\IdentityAccess\Http\Controllers\AuthController;
use Modules\IdentityAccess\Http\Controllers\UserController;
use Modules\Invoice\Http\Controllers\CreditNoteController;
use Modules\Invoice\Http\Controllers\InvoiceController;
use Modules\Payment\Http\Controllers\PaymentController;
use Modules\Shared\Http\Controllers\AuditLogController;
use Modules\Shared\Http\Middleware\IdempotencyMiddleware;
use Modules\Subscription\Http\Controllers\AdminCatalogController;
use Modules\Subscription\Http\Controllers\CatalogController;
use Modules\Subscription\Http\Controllers\SubscriptionController;
use Modules\Webhook\Http\Controllers\StripeWebhookController;
use Modules\Webhook\Http\Controllers\TenantWebhookConfigController;

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

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

        // Webhook Config
        Route::get('/webhook-config', [TenantWebhookConfigController::class, 'show']);
        Route::put('/webhook-config', [TenantWebhookConfigController::class, 'update']);

        // Subscriptions
        Route::middleware(IdempotencyMiddleware::class)->group(function () {
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::post('/invoices/{invoice}/finalize', [InvoiceController::class, 'finalize']);
            Route::post('/invoices/{invoice}/credit-notes', [CreditNoteController::class, 'store']);
        });
    });
});
