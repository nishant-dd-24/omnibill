<?php

declare(strict_types=1);

namespace Modules\Webhook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Domain\Context\CurrentTenant;
use Modules\Tenant\Application\Services\UpdateTenantSettingsService;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Domain\Models\TenantSettings;
use Modules\Webhook\Http\Requests\UpdateWebhookConfigRequest;

class TenantWebhookConfigController extends Controller
{
    public function show(): JsonResponse
    {
        /** @var TenantSettings|null $settings */
        $settings = TenantSettings::first();

        return response()->json([
            'data' => [
                'webhook_url' => $settings?->settings['webhook_url'] ?? null,
            ],
        ]);
    }

    public function update(
        UpdateWebhookConfigRequest $request,
        CurrentTenant $currentTenant,
        UpdateTenantSettingsService $updateService
    ): JsonResponse {
        /** @var Tenant $tenant */
        $tenant = Tenant::findOrFail($currentTenant->id());

        $updateService->execute($tenant, [
            'webhook_url' => $request->validated('webhook_url'),
        ]);

        /** @var TenantSettings|null $settings */
        $settings = TenantSettings::first();

        return response()->json([
            'data' => [
                'webhook_url' => $settings?->settings['webhook_url'] ?? null,
            ],
        ]);
    }
}
