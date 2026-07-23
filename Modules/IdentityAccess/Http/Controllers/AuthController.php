<?php

namespace Modules\IdentityAccess\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\IdentityAccess\Application\Services\TokenManager;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\IdentityAccess\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function __construct(private readonly TokenManager $tokenManager) {}

    public function login(LoginRequest $request): JsonResponse
    {
        /** @var string $email */
        $email = $request->validated('email');
        $user = User::where('email', $email)->first();

        /** @var string $password */
        $password = $request->validated('password');
        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var string $deviceName */
        $deviceName = $request->validated('device_name', 'default');
        $token = $this->tokenManager->issue($user, $deviceName);

        return response()->json([
            'token' => $token->plainTextToken,
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = request()->user();
        if ($user instanceof User) {
            $this->tokenManager->revokeCurrent($user);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(): JsonResponse
    {
        return response()->json(['data' => request()->user()]);
    }
}
