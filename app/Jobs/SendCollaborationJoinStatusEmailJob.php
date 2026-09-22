<?php

namespace App\Jobs;

use App\Models\CollaborationJoinRequest;
use App\Notifications\CollaborationJoinAcceptedNotification;
use App\Notifications\CollaborationJoinRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Queued job: email after admin accepts or rejects a collaboration request.
 */
class SendCollaborationJoinStatusEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $requestId  collaboration_join_requests.id
     * @param  string  $decision  accepted|rejected (maps from approved/rejected status)
     */
    public function __construct(
        public int $requestId,
        public string $decision,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        if (! in_array($this->decision, ['accepted', 'rejected'], true)) {
            return;
        }

        $request = CollaborationJoinRequest::query()->find($this->requestId);
        if (! $request) {
            Log::warning('SendCollaborationJoinStatusEmailJob: request missing', [
                'id' => $this->requestId,
            ]);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email === '') {
            return;
        }

        $notification = $this->decision === 'accepted'
            ? new CollaborationJoinAcceptedNotification($request)
            : new CollaborationJoinRejectedNotification($request);

        Notification::route('mail', $email)->notify($notification);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendCollaborationJoinStatusEmailJob failed', [
            'request_id' => $this->requestId,
            'decision' => $this->decision,
            'error' => $exception?->getMessage(),
        ]);
    }
}
