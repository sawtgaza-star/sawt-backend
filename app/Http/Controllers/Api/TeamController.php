<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MajorResource;
use App\Http\Resources\TeamMemberResource;
use App\Services\TeamService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $team,
    ) {}

    /**
     * Team page: majors (tabs) + members.
     * Query: ?major=design  OR  ?major_uuid=xxxxx
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payload = $this->team->page(
                majorSlug: $request->query('major'),
                majorUuid: $request->query('major_uuid'),
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد قسم (major) بهذا الاسم.',
                'error' => 'major_not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'majors' => MajorResource::collection($payload['majors']),
                'members' => TeamMemberResource::collection($payload['members']),
            ],
        ]);
    }
}
