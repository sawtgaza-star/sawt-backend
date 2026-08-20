<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentPageService;
use Illuminate\Http\JsonResponse;

class ContentPageController extends Controller
{
    public function __construct(
        protected ContentPageService $content,
    ) {}

    /**
     * Content page: hero + Instagram reels.
     */
    public function show(): JsonResponse
    {
        $payload = $this->content->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'reels' => $payload['reels'],
            ],
        ]);
    }
}
