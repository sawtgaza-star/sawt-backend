<?php

namespace App\Jobs;

use App\Models\CourseSubscribeRequest;
use App\Notifications\CourseSubscribeAcceptedNotification;
use App\Notifications\CourseSubscribeRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Queued job: email the applicant after admin accepts or rejects a course subscribe request.
 */
class SendCourseSubscribeStatusEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $subscribeRequestId  course_subscribe_requests.id
     * @param  string  $decision  accepted|rejected
     */
    public function __construct(
        public int $subscribeRequestId,
        public string $decision,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        if (! in_array($this->decision, ['accepted', 'rejected'], true)) {
            return;
        }

        $request = CourseSubscribeRequest::query()
            ->with(['course'])
            ->find($this->subscribeRequestId);

        if (! $request) {
            Log::warning('SendCourseSubscribeStatusEmailJob: request missing', [
                'id' => $this->subscribeRequestId,
            ]);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email === '') {
            return;
        }

        $notification = $this->decision === 'accepted'
            ? new CourseSubscribeAcceptedNotification($request)
            : new CourseSubscribeRejectedNotification($request);

        Notification::route('mail', $email)->notify($notification);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendCourseSubscribeStatusEmailJob failed', [
            'subscribe_request_id' => $this->subscribeRequestId,
            'decision' => $this->decision,
            'error' => $exception?->getMessage(),
        ]);
    }
}
