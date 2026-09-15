<?php

namespace App\Jobs;

use App\Models\CourseJoinRequest;
use App\Notifications\CourseJoinAcceptedNotification;
use App\Notifications\CourseJoinRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Queued job: email the applicant after admin accepts or rejects a course join request.
 * Pass only the request id — reload models in handle() to avoid stale serialized relations.
 */
class SendCourseJoinStatusEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $joinRequestId  course_join_requests.id
     * @param  string  $decision  accepted|rejected
     */
    public function __construct(
        public int $joinRequestId,
        public string $decision,
    ) {
        // Push the job only after the surrounding DB transaction commits
        $this->afterCommit();
    }

    /**
     * Send the matching notification to the user (or fallback email on the request).
     */
    public function handle(): void
    {
        $request = CourseJoinRequest::query()
            ->with(['course', 'user'])
            ->find($this->joinRequestId);

        if (! $request) {
            Log::warning('SendCourseJoinStatusEmailJob: join request missing', [
                'id' => $this->joinRequestId,
            ]);

            return;
        }

        $notification = $this->decision === 'accepted'
            ? new CourseJoinAcceptedNotification($request)
            : new CourseJoinRejectedNotification($request);

        if ($request->user) {
            $request->user->notify($notification);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email !== '') {
            Notification::route('mail', $email)->notify($notification);
        }
    }

    /**
     * Log permanent failures for admin debugging.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('SendCourseJoinStatusEmailJob failed', [
            'join_request_id' => $this->joinRequestId,
            'decision' => $this->decision,
            'error' => $exception?->getMessage(),
        ]);
    }
}
