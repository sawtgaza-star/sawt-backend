<?php

namespace App\Services;

use App\Jobs\SendCourseSubscribeStatusEmailJob;
use App\Models\Course;
use App\Models\CourseSubscribeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Guest course subscribe applications (اشترك الآن modal) — pending → accept/reject.
 * Separate from CourseJoinService (authenticated join / waitlist).
 * Acceptance queues an email to the applicant's address.
 */
class CourseSubscribeService
{
    /** Last queue-dispatch error (shown in Filament when job could not be queued). */
    public ?string $lastEmailError = null;

    /**
     * Store a pending subscribe request (or re-open a rejected one for the same email).
     *
     * @param  array{
     *     full_name: string,
     *     phone: string,
     *     phone_country_code?: string|null,
     *     email: string,
     *     academic_level: string,
     *     attended_similar_course: bool,
     *     goals_interests?: string|null,
     *     join_goal: string,
     *     additional_notes?: string|null
     * }  $data
     */
    public function submit(Course $course, array $data): CourseSubscribeRequest
    {
        if ($course->status !== 'published') {
            throw ValidationException::withMessages([
                'course' => 'هذا الكورس غير متاح حالياً.',
            ]);
        }

        if (! $course->is_coming_soon && ! $course->hasAvailableSeats()) {
            throw ValidationException::withMessages([
                'course' => 'اكتملت المقاعد المتاحة لهذا الكورس.',
            ]);
        }

        $email = strtolower(trim($data['email']));

        $existing = CourseSubscribeRequest::query()
            ->where('course_id', $course->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existing?->status === 'pending') {
            throw ValidationException::withMessages([
                'email' => 'لديك طلب اشتراك قيد المراجعة لهذا الكورس.',
            ]);
        }

        if ($existing?->status === 'accepted') {
            throw ValidationException::withMessages([
                'email' => 'تم قبول اشتراكك مسبقاً في هذا الكورس.',
            ]);
        }

        $payload = [
            'status' => 'pending',
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'phone_country_code' => $data['phone_country_code'] ?? null,
            'email' => $email,
            'academic_level' => $data['academic_level'],
            'attended_similar_course' => (bool) $data['attended_similar_course'],
            'goals_interests' => $data['goals_interests'] ?? null,
            'join_goal' => $data['join_goal'],
            'additional_notes' => $data['additional_notes'] ?? null,
            'admin_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh(['course']);
        }

        return CourseSubscribeRequest::create([
            'course_id' => $course->id,
            ...$payload,
        ])->load(['course']);
    }

    /**
     * Accept a pending subscribe request and queue the acceptance email.
     */
    public function accept(CourseSubscribeRequest $request, User $admin, ?string $adminNotes = null): CourseSubscribeRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('يمكن قبول الطلبات قيد الانتظار فقط.');
        }

        $this->lastEmailError = null;

        $request = DB::transaction(function () use ($request, $admin, $adminNotes) {
            $request->update([
                'status' => 'accepted',
                'admin_notes' => $adminNotes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $request->course()->increment('students_count');

            return $request->fresh(['course', 'reviewer']);
        });

        $this->queueStatusEmail($request, 'accepted');

        return $request;
    }

    /**
     * Reject a pending subscribe request, then queue the rejection email.
     */
    public function reject(CourseSubscribeRequest $request, User $admin, ?string $adminNotes = null): CourseSubscribeRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('يمكن رفض الطلبات قيد الانتظار فقط.');
        }

        $this->lastEmailError = null;

        $request->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $request = $request->fresh(['course', 'reviewer']);
        $this->queueStatusEmail($request, 'rejected');

        return $request;
    }

    /**
     * @param  string  $decision  accepted|rejected
     */
    protected function queueStatusEmail(CourseSubscribeRequest $request, string $decision): void
    {
        if (! in_array($decision, ['accepted', 'rejected'], true)) {
            return;
        }

        try {
            SendCourseSubscribeStatusEmailJob::dispatch($request->id, $decision);
        } catch (Throwable $e) {
            $this->lastEmailError = $e->getMessage();
            Log::error('Failed to dispatch course subscribe status email job', [
                'subscribe_request_id' => $request->id,
                'decision' => $decision,
                'error' => $e->getMessage(),
            ]);
        }
    }
}