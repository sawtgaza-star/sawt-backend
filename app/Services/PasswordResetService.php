<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public const CODE_TTL_SECONDS = 60;

    public const RESET_TOKEN_TTL_SECONDS = 600;

    public const MAX_SEND_ATTEMPTS = 5;

    public const SEND_LOCK_SECONDS = 180;

    public function __construct(
        protected UserRepositoryInterface $users,
    ) {}

    /**
     * @return array{expires_in: int, attempts_left: int}
     */
    public function sendCode(string $email): array
    {
        $user = $this->findResettableUser($email);
        $this->assertCanSend($user->email);

        $previousHash = $this->invalidatePreviousCode($user->email);
        $code = $this->makeUniqueCode($previousHash);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => 'otp|'.Hash::make($code),
                'created_at' => now(),
            ],
        );

        try {
            $user->notify(new PasswordResetCodeNotification($code, self::CODE_TTL_SECONDS));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset code.', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['تعذر إرسال رمز التحقق. حاول مرة أخرى.'],
            ]);
        }

        return [
            'expires_in' => self::CODE_TTL_SECONDS,
            'attempts_left' => $this->recordSendAttempt($user->email),
        ];
    }

    /**
     * Verify the OTP for this email only.
     *
     * @return array{reset_token: string, expires_in: int}
     */
    public function verifyCode(string $email, string $code): array
    {
        $user = $this->findResettableUser($email);
        $row = $this->tokenRow($user->email);

        if (! $row || ! str_starts_with((string) $row->token, 'otp|')) {
            throw ValidationException::withMessages([
                'code' => ['الرمز غير صحيح أو منتهي الصلاحية.'],
            ]);
        }

        if ($this->isExpired($row->created_at, self::CODE_TTL_SECONDS)) {
            throw ValidationException::withMessages([
                'code' => ['انتهت صلاحية الرمز. أعد إرساله.'],
            ]);
        }

        if (! Hash::check($code, substr((string) $row->token, 4))) {
            if ($this->isPreviousExpiredCode($user->email, $code)) {
                throw ValidationException::withMessages([
                    'code' => ['انتهت صلاحية الرمز السابق. استخدم الرمز الجديد.'],
                ]);
            }

            throw ValidationException::withMessages([
                'code' => ['الرمز غير صحيح أو منتهي الصلاحية.'],
            ]);
        }

        $resetToken = Str::random(64);

        DB::table('password_reset_tokens')->where('email', $user->email)->update([
            'token' => 'reset|'.$this->tokenLookup($resetToken),
            'created_at' => now(),
        ]);

        return [
            'reset_token' => $resetToken,
            'expires_in' => self::RESET_TOKEN_TTL_SECONDS,
        ];
    }

    public function resetPassword(string $resetToken, string $password): void
    {
        $row = $this->findResetRow($resetToken);

        if (! $row) {
            throw ValidationException::withMessages([
                'reset_token' => ['رمز إعادة التعيين غير صالح. أعد التحقق من الرمز.'],
            ]);
        }

        if ($this->isExpired($row->created_at, self::RESET_TOKEN_TTL_SECONDS)) {
            throw ValidationException::withMessages([
                'reset_token' => ['انتهت صلاحية إعادة التعيين. اطلب رمزاً جديداً.'],
            ]);
        }

        $user = $this->findResettableUser($row->email);

        $user->forceFill(['password' => $password])->save();
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
    }

    protected function findResettableUser(string $email): User
    {
        $email = Str::lower(trim($email));
        $user = $this->users->findByEmail($email);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['لا يوجد حساب بهذا البريد الإلكتروني.'],
            ]);
        }

        if ($user->isAdmin() || ! $user->hasAnyRole(User::WEBSITE_ROLES)) {
            throw ValidationException::withMessages([
                'email' => ['لا يوجد حساب بهذا البريد الإلكتروني.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['هذا الحساب غير مفعّل حالياً.'],
            ]);
        }

        return $user;
    }

    protected function assertCanSend(string $email): void
    {
        $email = Str::lower($email);
        $lockUntil = Cache::get($this->sendLockKey($email));

        if (is_numeric($lockUntil) && (int) $lockUntil > time()) {
            $this->throwSendLocked((int) $lockUntil - time());
        }

        if ((int) Cache::get($this->sendAttemptsKey($email), 0) >= self::MAX_SEND_ATTEMPTS) {
            $this->lockSending($email);
            $this->throwSendLocked(self::SEND_LOCK_SECONDS);
        }
    }

    protected function recordSendAttempt(string $email): int
    {
        $email = Str::lower($email);
        $attempts = (int) Cache::get($this->sendAttemptsKey($email), 0) + 1;

        if ($attempts >= self::MAX_SEND_ATTEMPTS) {
            Cache::forget($this->sendAttemptsKey($email));
            $this->lockSending($email);

            return 0;
        }

        Cache::put(
            $this->sendAttemptsKey($email),
            $attempts,
            now()->addSeconds(self::SEND_LOCK_SECONDS * 2),
        );

        return self::MAX_SEND_ATTEMPTS - $attempts;
    }

    protected function lockSending(string $email): void
    {
        Cache::put(
            $this->sendLockKey($email),
            time() + self::SEND_LOCK_SECONDS,
            self::SEND_LOCK_SECONDS,
        );
    }

    protected function throwSendLocked(int $retryAfter): void
    {
        $retryAfter = max(1, $retryAfter);
        $minutes = (int) ceil($retryAfter / 60);

        $exception = ValidationException::withMessages([
            'email' => ["لقد استنفدت المحاولات الخمس. يمكنك إعادة الإرسال بعد {$minutes} دقيقة."],
            'retry_after' => [(string) $retryAfter],
        ]);

        $exception->status(429);

        throw $exception;
    }

    protected function sendAttemptsKey(string $email): string
    {
        return 'password-reset:send-attempts:'.$email;
    }

    protected function sendLockKey(string $email): string
    {
        return 'password-reset:send-lock:'.$email;
    }

    protected function invalidatePreviousCode(string $email): ?string
    {
        $row = $this->tokenRow($email);
        $hash = null;

        if ($row && str_starts_with((string) $row->token, 'otp|')) {
            $hash = substr((string) $row->token, 4) ?: null;
        }

        if ($hash) {
            Cache::put(
                $this->expiredCodeKey($email),
                $hash,
                now()->addSeconds(self::CODE_TTL_SECONDS * 2),
            );
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return $hash;
    }

    protected function makeUniqueCode(?string $previousHash): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while ($previousHash && Hash::check($code, $previousHash));

        return $code;
    }

    protected function isPreviousExpiredCode(string $email, string $code): bool
    {
        $hash = Cache::get($this->expiredCodeKey($email));

        return is_string($hash) && $hash !== '' && Hash::check($code, $hash);
    }

    protected function expiredCodeKey(string $email): string
    {
        return 'password-reset:expired-otp:'.Str::lower($email);
    }

    protected function tokenRow(string $email): ?object
    {
        return DB::table('password_reset_tokens')->where('email', $email)->first();
    }

    protected function findResetRow(string $resetToken): ?object
    {
        return DB::table('password_reset_tokens')
            ->where('token', 'reset|'.$this->tokenLookup($resetToken))
            ->first();
    }

    protected function tokenLookup(string $plain): string
    {
        return hash_hmac('sha256', $plain, (string) config('app.key'));
    }

    protected function isExpired(mixed $createdAt, int $ttlSeconds): bool
    {
        if (! $createdAt) {
            return true;
        }

        return now()->greaterThan(Carbon::parse($createdAt)->addSeconds($ttlSeconds));
    }
}
