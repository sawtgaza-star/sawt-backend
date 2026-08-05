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
     * Team listing: hero + majors + members.
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
                'hero' => $payload['hero'],
                'filters' => $payload['filters'],
                'majors' => MajorResource::collection($payload['majors']),
                'members' => TeamMemberResource::collection($payload['members']),
            ],
        ]);
    }

    /**
     * Team member detail page (hero + profile + intro + related members).
     * Header / footer are not included.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $payload = $this->team->member($uuid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد عضو بهذا المعرّف.',
                'error' => 'member_not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'member' => new TeamMemberResource($payload['member']),
                'labels' => $payload['labels'],
                'intro' => $payload['intro'],
                'related' => [
                    'title' => $payload['related']['title'],
                    'view_all' => $payload['related']['view_all'],
                    'members' => TeamMemberResource::collection($payload['related']['members']),
                ],
            ],
        ]);
    }
}
