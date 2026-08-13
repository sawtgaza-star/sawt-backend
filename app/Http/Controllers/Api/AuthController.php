<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifyResetCodeRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected PasswordResetService $passwordReset,
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

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordReset->sendCode($request->validated('email'));

        return response()->json([
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
            'data' => $result,
        ]);
    }

    public function resendResetCode(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordReset->sendCode($request->validated('email'));

        return response()->json([
            'message' => 'تم إعادة إرسال رمز التحقق.',
            'data' => $result,
        ]);
    }

    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        $result = $this->passwordReset->verifyCode(
            $request->validated('email'),
            $request->validated('code'),
        );

        return response()->json([
            'message' => 'تم التحقق من الرمز بنجاح.',
            'data' => $result,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->passwordReset->resetPassword(
            $data['reset_token'],
            $data['password'],
        );

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.',
        ]);
    }
}
