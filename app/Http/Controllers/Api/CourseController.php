<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncubatorCourseCardResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public courses API under /api/v1/pages/courses.
 * Index = lean cards; show = full detail via CourseService (slug or uuid).
 */
class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courses,
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
}
