<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogListingCardResource;
use App\Http\Resources\HomeCreatorCardResource;
use App\Http\Resources\TeamListingCardResource;
use App\Services\HomePageService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __construct(
        protected HomePageService $home,
    ) {}

    /**
     * Homepage content from settings (+ creators/team lists).
     * Navbar / footer are not included — use GET /api/v1/layout/navbar and GET /api/v1/layout/footer.
     */
    public function show(): JsonResponse
    {
        $payload = $this->home->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'stats' => $payload['stats'],
                'who_we_are' => $payload['who_we_are'],
                'news' => [
                    'title' => $payload['news']['title'],
                    'subtitle' => $payload['news']['subtitle'],
                    'read_more' => $payload['news']['read_more'],
                    'view_all' => $payload['news']['view_all'],
                    'items' => BlogListingCardResource::collection($payload['news']['items']),
                ],
                'creators' => [
                    'title' => $payload['creators']['title'],
                    'description' => $payload['creators']['description'],
                    'view_all' => $payload['creators']['view_all'],
                    'experience_title' => $payload['creators']['experience_title'],
                    'followers_suffix' => $payload['creators']['followers_suffix'],
                    'items' => HomeCreatorCardResource::collection($payload['creators']['items']),
                ],
                'platform_sections' => $payload['platform_sections'],
                'partners' => $payload['partners'],
                'stories' => $payload['stories'],
                'team' => [
                    'title' => $payload['team']['title'],
                    'subtitle' => $payload['team']['subtitle'],
                    'profile_cta' => $payload['team']['profile_cta'],
                    'items' => TeamListingCardResource::collection($payload['team']['items']),
                ],
                'join_cta' => $payload['join_cta'],
                'reviews' => $payload['reviews'],
            ],
        ]);
    }
}
