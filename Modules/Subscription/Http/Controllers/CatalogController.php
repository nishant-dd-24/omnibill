<?php

declare(strict_types=1);

namespace Modules\Subscription\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Subscription\Application\Services\CatalogService;
use Modules\Subscription\Http\Resources\PlanResource;

class CatalogController extends Controller
{
    private CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index(): JsonResponse
    {
        $plans = $this->catalogService->getActivePlans();

        return response()->json([
            'data' => PlanResource::collection($plans),
        ]);
    }
}
