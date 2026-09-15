<?php

namespace App\Services;

use App\Jobs\SendCourseJoinStatusEmailJob;
use App\Models\Course;
use App\Models\CourseJoinRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Handles course join / waitlist requests (pending → accept/reject).
 * Coming-soon courses skip the seats check so waitlist signups stay open.
 * Status emails are dispatched via SendCourseJoinStatusEmailJob (database queue).
 */
class CourseJoinService
{
    /** Last queue-dispatch error (shown in Filament when job could not be queued). */
    public ?string $lastEmailError = null;

    /**
     * Create or re-open a pending join/waitlist request for the authenticated user.
     *
     * @param  array{full_name?: string, phone?: string|null, email?: string|null, message?: string|null}  $data
     */
    public function submit(Course $course, User $user, array $data): CourseJoinRequest
    {
        if ($course->status !== 'published') {
            throw ValidationException::withMessages([
                'course' => 'هذا الكورس غير متاح حالياً.',
            ]);
        }

        $existing = CourseJoinRequest::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing?->status === 'pending') {
            throw ValidationException::withMessages([
                'course' => 'لديك طلب انضمام قيد المراجعة لهذا الكورس.',
            ]);
        }

        if ($existing?->status === 'accepted' || $course->isJoinedBy($user)) {
            throw ValidationException::withMessages([
                'course' => 'أنت منضم بالفعل إلى هذا الكورس.',
            ]);
        }

        // Waitlist (coming soon) ignores seat capacity; open enrollment still enforces it
        if (! $course->is_coming_soon && ! $course->hasAvailableSeats()) {
            throw ValidationException::withMessages([
                'course' => 'اكتملت المقاعد المتاحة لهذا الكورس.',
            ]);
        }

        $payload = [
            'status' => 'pending',
            'full_name' => $data['full_name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'email' => $data['email'] ?? $user->email,
            'message' => $data['message'] ?? null,
            'admin_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh(['course', 'user']);
        }

        return CourseJoinRequest::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            ...$payload,
        ])->load(['course', 'user']);
    }

    /**
     * Accept a pending request, then queue the acceptance email job.
     */
    public function accept(CourseJoinRequest $request, User $admin, ?string $adminNotes = null): CourseJoinRequest
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

            return $request->fresh(['course', 'user']);
        });

        $this->queueStatusEmail($request, 'accepted');

        return $request;
    }

    /**
     * Reject a pending request, then queue the rejection email job.
     */
    public function reject(CourseJoinRequest $request, User $admin, ?string $adminNotes = null): CourseJoinRequest
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

        $request = $request->fresh(['course', 'user']);
        $this->queueStatusEmail($request, 'rejected');

        return $request;
    }

    /**
     * Dispatch SendCourseJoinStatusEmailJob onto the database queue.
     *
     * @param  string  $decision  accepted|rejected
     */
    protected function queueStatusEmail(CourseJoinRequest $request, string $decision): void
    {
        try {
            SendCourseJoinStatusEmailJob::dispatch($request->id, $decision);
        } catch (Throwable $e) {
            $this->lastEmailError = $e->getMessage();
            Log::error('Failed to dispatch course join status email job', [
                'join_request_id' => $request->id,
                'decision' => $decision,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
