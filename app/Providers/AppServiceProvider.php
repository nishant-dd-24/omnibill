<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\IdentityAccess\Domain\Enums\Role;
use Modules\IdentityAccess\Domain\Models\Token;
use Modules\Invoice\Application\Adapters\InvoiceRendererInterface;
use Modules\Invoice\Infrastructure\Adapters\DompdfInvoiceRenderer;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Shared\Domain\Context\TenantConfig;
use Modules\Shared\Domain\Contracts\GetTenantSettings;
use Modules\Subscription\Application\Adapters\BillingGatewayInterface;
use Modules\Subscription\Infrastructure\Adapters\StripeAdapter;
use Modules\Tenant\Infrastructure\CachingGetTenantSettings;
use Modules\Tenant\Infrastructure\Database\PostgresRlsManager;
use Ramsey\Uuid\Uuid;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GetTenantSettings::class, CachingGetTenantSettings::class);
        $this->app->bind(
            BillingGatewayInterface::class,
            StripeAdapter::class
        );
        $this->app->bind(
            InvoiceRendererInterface::class,
            DompdfInvoiceRenderer::class
        );
        $this->app->singleton(TenantConfig::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('api', function (Request $request) {
            if (app()->bound(CurrentTenant::class) && app(CurrentTenant::class)->hasTenant()) {
                $tenantId = app(CurrentTenant::class)->id();
                // Default to 1000 per minute, override via TenantConfig if plan provides it
                $limitValue = app(TenantConfig::class)->get('rate_limit', 1000);
                $limit = is_numeric($limitValue) ? (int) $limitValue : 1000;

                return Limit::perMinute($limit)->by($tenantId);
            }

            // IP based for unauthenticated/unresolved tenant requests (e.g., login)
            return Limit::perMinute(60)->by($request->ip());
        });

        Str::createUuidsUsing(function () {
            return Uuid::uuid7();
        });

        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            // e.g. Modules\Invoice\Domain\Models\Invoice => Modules\Invoice\Http\Policies\InvoicePolicy
            if (str_starts_with($modelClass, 'Modules\\')) {
                return str_replace(
                    '\\Domain\\Models\\',
                    '\\Http\\Policies\\',
                    $modelClass
                ).'Policy';
            }

            return 'App\\Policies\\'.class_basename($modelClass).'Policy';
        });

        Sanctum::usePersonalAccessTokenModel(Token::class);

        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });

        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        Gate::define('viewPulse', function ($user = null) {
            return $user !== null && $user->hasRole(Role::SUPER_ADMIN);
        });

        $checks = [
            DatabaseCheck::new(),
            CacheCheck::new(),
            QueueCheck::new(),
            PingCheck::new()->url('https://api.stripe.com'),
        ];

        if (extension_loaded('redis')) {
            $checks[] = RedisCheck::new();
        }

        Health::checks($checks);

        app('events')->listen(
            [JobProcessing::class, JobProcessed::class, JobExceptionOccurred::class, JobFailed::class],
            function () {
                app(PostgresRlsManager::class)->clearTenantContext();
            }
        );
    }
}
