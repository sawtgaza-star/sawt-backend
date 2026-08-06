<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $users,
    ) {}

    /**
     * @return array{user: User, access_token: string, token_type: string, expires_in: int}
     */
    public function register(array $data): array
    {
        $user = $this->users->create([
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->tokenResponse($user, $token);
    }

    /**
     * @return array{user: User, access_token: string, token_type: string, expires_in: int}
     */
    public function login(array $credentials): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        if ($user->status !== 'active') {
            $message = match ($user->status) {
                'banned' => 'تم حظر هذا الحساب. تواصل مع الإدارة.',
                'inactive' => 'هذا الحساب غير مفعّل حالياً.',
                default => 'لا يمكن تسجيل الدخول بهذا الحساب.',
            };

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return $this->tokenResponse($user, $token);
    }

    /**
     * Invalidate the current JWT (logout).
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * @return array{user: User, access_token: string, token_type: string, expires_in: int}
     */
    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        $user = JWTAuth::setToken($token)->toUser();

        return $this->tokenResponse($user, $token);
    }

    public function me(): User
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * @return array{user: User, access_token: string, token_type: string, expires_in: int}
     */
    protected function tokenResponse(User $user, string $token): array
    {
        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
        ];
    }
}
