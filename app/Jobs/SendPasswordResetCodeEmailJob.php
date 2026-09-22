<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Queued job: send password-reset OTP email.
 * Dispatched immediately when the user requests a code (auth forgot-password).
 */
class SendPasswordResetCodeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $userId
     * @param  string  $code  plain OTP (only held in the job payload until sent)
     * @param  int  $expiresInSeconds
     */
    public function __construct(
        public int $userId,
        public string $code,
        public int $expiresInSeconds = 60,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);
        if (! $user?->email) {
            Log::warning('SendPasswordResetCodeEmailJob: user missing', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        $user->notify(new PasswordResetCodeNotification($this->code, $this->expiresInSeconds));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendPasswordResetCodeEmailJob failed', [
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
