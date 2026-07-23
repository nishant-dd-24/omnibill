<?php

declare(strict_types=1);

namespace Modules\Customer\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Customer\Application\Services\CreateCustomerService;
use Modules\Customer\Application\Services\DeleteCustomerService;
use Modules\Customer\Application\Services\UpdateCustomerService;
use Modules\Customer\Domain\Models\Customer;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::paginate();

        return response()->json([
            'data' => CustomerResource::collection($customers),
            'meta' => [
                'total' => $customers->total(),
            ],
        ]);
    }

    public function store(StoreCustomerRequest $request, CreateCustomerService $service): CustomerResource
    {
        $this->authorize('create', Customer::class);

        $customer = $service->execute($request->validated());

        return new CustomerResource($customer);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        UpdateCustomerService $service
    ): CustomerResource {
        $this->authorize('update', $customer);

        $customer = $service->execute($customer, $request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer, DeleteCustomerService $service): JsonResponse
    {
        $this->authorize('delete', $customer);

        $service->execute($customer);

        return response()->json(null, 204);
    }
}
