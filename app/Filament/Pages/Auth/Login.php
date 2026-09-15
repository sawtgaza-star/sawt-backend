<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Filament admin login with failure-only throttling + on-page countdown timer.
 *
 * Keyed by IP + email (config/admin.php). When locked, the UI shows a live
 * seconds/minutes timer and disables the Sign in button until it reaches zero.
 */
class Login extends BaseLogin
{
    /**
     * Custom blade with lockout banner + wire:poll countdown.
     *
     * @var view-string
     */
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Seconds left before the next login attempt is allowed (null = not locked).
     */
    public ?int $secondsUntilRetry = null;

    /**
     * Authenticate with failure-only rate limiting and start the UI timer when locked.
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $email = strtolower((string) ($data['email'] ?? ''));

        try {
            $this->ensureLoginIsNotRateLimited($email);
        } catch (TooManyRequestsException $exception) {
            $this->startLockoutTimer($exception->secondsUntilAvailable);
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->hitLoginRateLimiter($email);
            $this->syncLockoutTimerFromLimiter($email);

            // Lockout just started — show timer banner (avoid ValidationException wiping Livewire state)
            if (($this->secondsUntilRetry ?? 0) > 0) {
                $this->getRateLimitedNotification(new TooManyRequestsException(
                    static::class,
                    'authenticate',
                    request()->ip(),
                    (int) $this->secondsUntilRetry,
                ))?->send();

                return null;
            }

            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        // Same panel gate as stock Filament — non-admins get a generic failure.
        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            $this->hitLoginRateLimiter($email);
            $this->syncLockoutTimerFromLimiter($email);

            if (($this->secondsUntilRetry ?? 0) > 0) {
                $this->getRateLimitedNotification(new TooManyRequestsException(
                    static::class,
                    'authenticate',
                    request()->ip(),
                    (int) $this->secondsUntilRetry,
                ))?->send();

                return null;
            }

            $this->throwFailureValidationException();
        }

        $this->clearLoginRateLimiter($email);
        $this->secondsUntilRetry = null;
        session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * Livewire poll target — ticks the on-page countdown every second.
     */
    public function tickLockoutTimer(): void
    {
        if ($this->secondsUntilRetry === null) {
            return;
        }

        if ($this->secondsUntilRetry <= 1) {
            $this->secondsUntilRetry = null;

            return;
        }

        $this->secondsUntilRetry--;
    }

    /**
     * Human-readable mm:ss (or just seconds under 60) for the banner.
     */
    public function getLockoutTimerLabelProperty(): string
    {
        $seconds = (int) ($this->secondsUntilRetry ?? 0);
        if ($seconds <= 0) {
            return '0:00';
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    /**
     * Disable Sign in while the lockout timer is running.
     */
    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->disabled(fn (): bool => ($this->secondsUntilRetry ?? 0) > 0);
    }

    /**
     * Cache key: one bucket per client IP + attempted email.
     */
    protected function loginRateLimitKey(string $email): string
    {
        return 'filament-admin-login:'.sha1(request()->ip().'|'.$email);
    }

    /**
     * @throws TooManyRequestsException
     */
    protected function ensureLoginIsNotRateLimited(string $email): void
    {
        $key = $this->loginRateLimitKey($email);
        $max = max(1, (int) config('admin.login_max_attempts', 5));

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new TooManyRequestsException(
                static::class,
                'authenticate',
                request()->ip(),
                RateLimiter::availableIn($key),
            );
        }
    }

    /**
     * Record a failed sign-in toward the lockout window.
     */
    protected function hitLoginRateLimiter(string $email): void
    {
        $decay = max(1, (int) config('admin.login_decay_seconds', 60));

        RateLimiter::hit($this->loginRateLimitKey($email), $decay);
    }

    /**
     * If this failure crossed the limit, start the visible countdown.
     */
    protected function syncLockoutTimerFromLimiter(string $email): void
    {
        $key = $this->loginRateLimitKey($email);
        $max = max(1, (int) config('admin.login_max_attempts', 5));

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $this->startLockoutTimer(RateLimiter::availableIn($key));
        }
    }

    /**
     * Show the lockout banner and begin polling.
     */
    protected function startLockoutTimer(int $seconds): void
    {
        $this->secondsUntilRetry = max(1, $seconds);
    }

    /**
     * Reset the counter after a successful admin sign-in.
     */
    protected function clearLoginRateLimiter(string $email): void
    {
        RateLimiter::clear($this->loginRateLimitKey($email));
    }

    /**
     * Keep Filament's failed-login message (do not reveal whether the email exists).
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
