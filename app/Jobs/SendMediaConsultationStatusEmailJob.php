<?php

namespace App\Jobs;

use App\Models\MediaConsultationRequest;
use App\Notifications\MediaConsultationAcceptedNotification;
use App\Notifications\MediaConsultationRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Queued job: email after admin accepts or rejects a media consultation request.
 */
class SendMediaConsultationStatusEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $requestId  media_consultation_requests.id
     * @param  string  $decision  accepted|rejected
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

        $request = MediaConsultationRequest::query()->find($this->requestId);
        if (! $request) {
            Log::warning('SendMediaConsultationStatusEmailJob: request missing', [
                'id' => $this->requestId,
            ]);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email === '') {
            return;
        }

        $notification = $this->decision === 'accepted'
            ? new MediaConsultationAcceptedNotification($request)
            : new MediaConsultationRejectedNotification($request);

        Notification::route('mail', $email)->notify($notification);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendMediaConsultationStatusEmailJob failed', [
            'request_id' => $this->requestId,
            'decision' => $this->decision,
            'error' => $exception?->getMessage(),
        ]);
    }
}
