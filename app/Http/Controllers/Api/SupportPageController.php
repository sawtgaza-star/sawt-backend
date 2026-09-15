<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoryListingCardResource;
use App\Services\SupportPageService;
use Illuminate\Http\JsonResponse;

/**
 * Public Support landing — GET /api/v1/pages/support.
 * Wizard / PayPal stay under /api/v1/support/* (unchanged).
 */
class SupportPageController extends Controller
{
    public function __construct(
        protected SupportPageService $supportPage,
    ) {}

    /**
     * Full marketing landing for ادعم صوت (hero → FAQ).
     */
    public function show(): JsonResponse
    {
        $payload = $this->supportPage->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'plans' => $payload['plans'],
                'impact' => $payload['impact'],
                'trust' => $payload['trust'],
                'community_goal' => $payload['community_goal'],
                'fund_allocation' => $payload['fund_allocation'],
                'partners' => $payload['partners'],
                'sponsor' => $payload['sponsor'],
                'stories' => [
                    'title' => $payload['stories']['title'],
                    'subtitle' => $payload['stories']['subtitle'],
                    'cta' => $payload['stories']['cta'],
                    'items' => StoryListingCardResource::collection($payload['stories']['items']),
                ],
                'faq' => $payload['faq'],
                'contact_cta' => $payload['contact_cta'],
                'methods' => $payload['methods'],
            ],
        ]);
    }
}
