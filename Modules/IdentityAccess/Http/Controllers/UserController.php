<?php

declare(strict_types=1);

namespace Modules\IdentityAccess\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\IdentityAccess\Application\Services\CreateUser;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\IdentityAccess\Http\Requests\StoreUserRequest;
use Modules\IdentityAccess\Http\Requests\UpdateUserRequest;
use Modules\IdentityAccess\Http\Resources\UserResource;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CreateUser $createUser) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::paginate();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $this->authorize('create', User::class);

        /** @var string $name */
        $name = $request->validated('name');
        /** @var string $email */
        $email = $request->validated('email');
        /** @var string $password */
        $password = $request->validated('password');

        $user = $this->createUser->execute(
            $name,
            $email,
            $password,
            $request->validatedRole()
        );

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }
}
