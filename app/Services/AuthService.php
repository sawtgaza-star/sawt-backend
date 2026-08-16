<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\WebsiteUserPermissions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            'country_code' => $data['country_code'] ?? '+970',
            'password' => $data['password'],
            'status' => 'active',
            'type' => User::TYPE_USER,
        ]);

        $user->assignRole($this->ensureUserRole());

        $user = $user->fresh()->load(['roles', 'creator.socials']);
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

        if (! $user->isActive()) {
            $message = match ($user->status) {
                'banned' => 'تم حظر هذا الحساب. تواصل مع الإدارة.',
                'inactive' => 'هذا الحساب غير مفعّل حالياً.',
                default => 'لا يمكن تسجيل الدخول بهذا الحساب.',
            };

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        $this->assertApiUser($user);

        $user->load(['roles', 'creator.socials']);
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

        $this->assertApiUser($user);

        return $this->tokenResponse($user->load(['roles', 'creator.socials']), $token);
    }

    public function me(): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        $this->assertApiUser($user);

        return $user->load(['roles', 'creator.socials']);
    }

    /**
     * API auth for website roles (user / content_creator), not Filament staff.
     */
    protected function assertApiUser(User $user): void
    {
        if ($user->isAdmin() || ! $user->hasAnyRole(User::WEBSITE_ROLES)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }
    }

    protected function ensureUserRole(): Role
    {
        $permissions = WebsiteUserPermissions::all();

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => User::ROLE_USER, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        return $role;
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
