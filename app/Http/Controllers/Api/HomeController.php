<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCardResource;
use App\Http\Resources\TeamMemberResource;
use App\Services\HomePageService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __construct(
        protected HomePageService $home,
    ) {}

    /**
     * Homepage content from settings (+ creators/team lists).
     * Header / footer are not included — use GET /api/v1/layout.
     */
    public function show(): JsonResponse
    {
        $payload = $this->home->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'stats' => $payload['stats'],
                'who_we_are' => $payload['who_we_are'],
                'news' => $payload['news'],
                'creators' => [
                    'title' => $payload['creators']['title'],
                    'description' => $payload['creators']['description'],
                    'view_all' => $payload['creators']['view_all'],
                    'items' => CreatorCardResource::collection($payload['creators']['items']),
                ],
                'platform_sections' => $payload['platform_sections'],
                'partners' => $payload['partners'],
                'stories' => $payload['stories'],
                'team' => [
                    'title' => $payload['team']['title'],
                    'subtitle' => $payload['team']['subtitle'],
                    'profile_cta' => $payload['team']['profile_cta'],
                    'items' => TeamMemberResource::collection($payload['team']['items']),
                ],
                'join_cta' => $payload['join_cta'],
                'reviews' => $payload['reviews'],
            ],
        ]);
    }
}
