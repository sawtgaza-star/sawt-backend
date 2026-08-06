<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseJoinRequest;
use App\Models\User;
use App\Notifications\CourseJoinAcceptedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CourseJoinService
{
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

        if (! $course->hasAvailableSeats()) {
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
            $request->user->notify(new CourseJoinAcceptedNotification($request));

            return $request;
        });
    }

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

        return $request->fresh(['course', 'user']);
    }
}
