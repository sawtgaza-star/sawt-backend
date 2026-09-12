<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseJoinRequest;
use App\Models\User;
use App\Notifications\CourseJoinAcceptedNotification;
use App\Notifications\CourseJoinRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Handles course join / waitlist requests (pending → accept/reject).
 * Coming-soon courses skip the seats check so waitlist signups stay open.
 */
class CourseJoinService
{
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
     * Accept a pending request (increments students_count + notifies the user).
     */
    public function accept(CourseJoinRequest $request, User $admin, ?string $adminNotes = null): CourseJoinRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('يمكن قبول الطلبات قيد الانتظار فقط.');
        }

        return DB::transaction(function () use ($request, $admin, $adminNotes) {
            $request->update([
                'status' => 'accepted',
                'admin_notes' => $adminNotes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $request->course()->increment('students_count');

            $request->load(['course', 'user']);
            $this->notifyApplicant($request, new CourseJoinAcceptedNotification($request));

            return $request;
        });
    }

    /**
     * Reject a pending request with optional admin notes + email the applicant.
     */
    public function reject(CourseJoinRequest $request, User $admin, ?string $adminNotes = null): CourseJoinRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException('يمكن رفض الطلبات قيد الانتظار فقط.');
        }

        $request->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $request = $request->fresh(['course', 'user']);
        $this->notifyApplicant($request, new CourseJoinRejectedNotification($request));

        return $request;
    }

    /**
     * Prefer the linked user account; fall back to the email on the join request.
     */
    protected function notifyApplicant(CourseJoinRequest $request, object $notification): void
    {
        if ($request->user) {
            $request->user->notify($notification);

            return;
        }

        $email = trim((string) ($request->email ?? ''));
        if ($email !== '') {
            Notification::route('mail', $email)->notify($notification);
        }
    }
}
