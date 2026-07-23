<?php

declare(strict_types=1);

namespace Modules\Tenant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Tenant\Application\Services\CreateTenantService;
use Modules\Tenant\Application\Services\UpdateTenantService;
use Modules\Tenant\Domain\Models\Tenant;
use Modules\Tenant\Http\Requests\StoreTenantRequest;
use Modules\Tenant\Http\Requests\UpdateTenantRequest;
use Modules\Tenant\Http\Resources\TenantResource;

class TenantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Tenant::class);

        return TenantResource::collection(Tenant::paginate());
    }

    public function store(StoreTenantRequest $request, CreateTenantService $service): TenantResource
    {
        Gate::authorize('create', Tenant::class);

        /** @var array{name: string} $data */
        $data = $request->validated();
        $tenant = $service->execute($data);

        return new TenantResource($tenant);
    }

    public function show(Tenant $tenant): TenantResource
    {
        Gate::authorize('view', $tenant);

        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant, UpdateTenantService $service): TenantResource
    {
        Gate::authorize('update', $tenant);

        /** @var array{name?: string} $data */
        $data = $request->validated();
        $tenant = $service->execute($tenant, $data);

        return new TenantResource($tenant);
    }
}
