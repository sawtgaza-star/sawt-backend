<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح.',
            'data' => [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->validated());

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($this->auth->me()),
            ],
        ]);
    }

    public function refresh(): JsonResponse
    {
        try {
            $result = $this->auth->refresh();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'تعذر تجديد التوكن.',
                'error' => $e->getMessage(),
            ], 401);
        }

        return response()->json([
            'message' => 'تم تجديد التوكن بنجاح.',
            'data' => [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            $this->auth->logout();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'تعذر تسجيل الخروج.',
                'error' => $e->getMessage(),
            ], 401);
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}
