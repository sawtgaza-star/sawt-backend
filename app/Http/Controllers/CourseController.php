<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->published()
            ->with(['instructor', 'category'])
            ->latest()
            ->paginate(12);

        return view('courses.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        abort_unless($course->status === 'published', 404);

        $course->load(['instructor', 'category']);
        $user = auth()->user();
        $joinRequest = $course->joinRequestFor($user);

        return view('courses.show', compact('course', 'joinRequest'));
    }

    public function join(Request $request, Course $course, CourseJoinService $joins): RedirectResponse
    {
        abort_unless($course->status === 'published', 404);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $joins->submit($course, $request->user(), $data);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'تم إرسال طلب الانضمام بنجاح. سيتم إشعارك بعد مراجعة الإدارة.');
    }
}
