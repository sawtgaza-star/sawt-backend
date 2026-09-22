<?php

namespace App\Jobs;

use App\Models\CreatorJoinRequest;
use App\Models\User;
use App\Notifications\CreatorJoinAcceptedNotification;
use App\Notifications\CreatorJoinRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Queued job: creator join accept/reject emails.
 *
 * Accept: join request is deleted after approve — pass userId + name + temp password.
 * Reject: pass joinRequestId and reload in handle().
 */
class SendCreatorJoinStatusEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $decision  accepted|rejected
     * @param  int|null  $joinRequestId  required for rejected
     * @param  int|null  $userId  required for accepted
     * @param  string|null  $applicantName  display name for accepted mail
     * @param  string|null  $temporaryPassword  plain password for new accounts
     */
    public function __construct(
        public string $decision,
        public ?int $joinRequestId = null,
        public ?int $userId = null,
        public ?string $applicantName = null,
        public ?string $temporaryPassword = null,
    ) {
        $this->afterCommit();
    }

    /** Factory: queue acceptance email (call before deleting the join request). */
    public static function dispatchAccepted(User $user, string $applicantName, ?string $temporaryPassword = null): void
    {
        static::dispatch(
            decision: 'accepted',
            userId: $user->id,
            applicantName: $applicantName,
            temporaryPassword: $temporaryPassword,
        );
    }

    /** Factory: queue rejection email. */
    public static function dispatchRejected(int $joinRequestId): void
    {
        static::dispatch(
            decision: 'rejected',
            joinRequestId: $joinRequestId,
        );
    }

    public function handle(): void
    {
        if ($this->decision === 'accepted') {
            $this->sendAccepted();

            return;
        }

        if ($this->decision === 'rejected') {
            $this->sendRejected();
        }
    }

    protected function sendAccepted(): void
    {
        $user = User::query()->find($this->userId);
        if (! $user?->email) {
            Log::warning('SendCreatorJoinStatusEmailJob: user missing for accept', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        $user->notify(new CreatorJoinAcceptedNotification(
            applicantName: $this->applicantName ?: $user->name,
            temporaryPassword: $this->temporaryPassword,
        ));
    }

    protected function sendRejected(): void
    {
        $request = CreatorJoinRequest::query()->find($this->joinRequestId);
        if (! $request) {
            Log::warning('SendCreatorJoinStatusEmailJob: request missing for reject', [
                'id' => $this->joinRequestId,
            ]);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email === '') {
            return;
        }

        Notification::route('mail', $email)
            ->notify(new CreatorJoinRejectedNotification($request));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendCreatorJoinStatusEmailJob failed', [
            'decision' => $this->decision,
            'join_request_id' => $this->joinRequestId,
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
