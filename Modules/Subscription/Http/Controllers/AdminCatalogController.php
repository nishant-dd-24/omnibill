<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Subscription\Application\Services\CatalogService;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Http\Resources\PlanResource;

class AdminCatalogController extends Controller
{
    private CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'stripe_product_id' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $plan = Plan::create($validated);
        $this->catalogService->clearCache();

        return response()->json([
            'data' => new PlanResource($plan),
        ], 201);
    }
}
