<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCourseJoinRequest;
use App\Http\Requests\Api\StoreCourseSubscribeRequest;
use App\Http\Resources\CourseJoinRequestResource;
use App\Http\Resources\CourseSubscribeRequestResource;
use App\Http\Resources\IncubatorCourseCardResource;
use App\Models\Course;
use App\Services\CourseJoinService;
use App\Services\CourseService;
use App\Services\CourseSubscribeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public courses API under /api/v1/pages/courses.
 * Index = lean cards; show = full detail;
 * join = authenticated waitlist; subscribe = guest "اشترك الآن" 3-step modal.
 */
class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courses,
        protected CourseJoinService $joins,
        protected CourseSubscribeService $subscribes,
    ) {}

    /**
     * Paginated published courses listing.
     *
     * Query: ?page=1&per_page=12
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, $request->integer('per_page') ?: 12));

        $items = Course::query()
            ->published()
            ->with(['courseCategory', 'trainer'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => [
                'items' => IncubatorCourseCardResource::collection($items->items()),
            ],
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    /**
     * Course detail — {slug} accepts slug or uuid (404 if unpublished/missing).
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $data = $this->courses->detailBySlug($slug);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'الكورس غير موجود.',
                'error' => 'course_not_found',
            ], 404);
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Join waitlist or request enrollment (requires JWT auth:api).
     * Front should send users to login/register on 401, then retry this endpoint.
     */
    public function join(StoreCourseJoinRequest $request, string $slug): JsonResponse
    {
        $course = Course::query()
            ->published()
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)->orWhere('uuid', $slug);
            })
            ->first();

        if (! $course) {
            return response()->json([
                'message' => 'الكورس غير موجود.',
                'error' => 'course_not_found',
            ], 404);
        }

        $joinRequest = $this->joins->submit($course, $request->user(), $request->validated());
        $joinRequest->loadMissing(['course', 'user']);

        $payload = (new CourseJoinRequestResource($joinRequest))->resolve();

        return response()->json([
            'message' => $payload['confirmation']['title'] ?? 'تم بنجاح',
            'data' => $payload,
        ], 201);
    }

    /**
     * Guest subscribe (اشترك الآن) — no auth. One API call for the 3-step modal fields.
     * Creates a pending CourseSubscribeRequest for Filament accept/reject.
     */
    public function subscribe(StoreCourseSubscribeRequest $request, string $slug): JsonResponse
    {
        $course = Course::query()
            ->published()
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)->orWhere('uuid', $slug);
            })
            ->first();

        if (! $course) {
            return response()->json([
                'message' => 'الكورس غير موجود.',
                'error' => 'course_not_found',
            ], 404);
        }

        $subscribeRequest = $this->subscribes->submit($course, $request->validated());
        $subscribeRequest->loadMissing(['course']);

        $payload = (new CourseSubscribeRequestResource($subscribeRequest))->resolve();

        return response()->json([
            'message' => $payload['confirmation']['title'] ?? 'تم بنجاح',
            'data' => $payload,
        ], 201);
    }
}
