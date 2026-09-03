<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncubatorCourseCardResource;
use App\Services\IncubatorService;
use Illuminate\Http\JsonResponse;

/**
 * Public incubator landing API — GET /api/v1/pages/incubator.
 * Chrome (navbar/footer) is separate: GET /api/v1/layout/incubator.
 */
class IncubatorController extends Controller
{
    public function __construct(
        protected IncubatorService $incubator,
    ) {}

    /**
     * Assemble landing JSON including FAQ accordion; course cards use IncubatorCourseCardResource.
     */
    public function show(): JsonResponse
    {
        $payload = $this->incubator->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'stats' => $payload['stats'],
                'why' => $payload['why'],
                'courses' => [
                    'title' => $payload['courses']['title'],
                    'subtitle' => $payload['courses']['subtitle'],
                    'items' => IncubatorCourseCardResource::collection($payload['courses']['items']),
                ],
                'sponsor' => $payload['sponsor'],
                'events' => $payload['events'],
                'gallery' => $payload['gallery'],
                'experts' => $payload['experts'],
                'faq' => $payload['faq'],
                'employers' => $payload['employers'],
                'join_cta' => $payload['join_cta'],
                'testimonials' => $payload['testimonials'],
            ],
        ]);
    }
}
