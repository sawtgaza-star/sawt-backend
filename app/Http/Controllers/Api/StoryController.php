<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoryListingCardResource;
use App\Http\Resources\StoryResource;
use App\Services\StoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        protected StoryService $stories,
    ) {}

    /**
     * Stories listing page.
     *
     * Query: ?page=1&per_page=12&category=slug&q=search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payload = $this->stories->listingPage(
                categorySlug: $request->query('category'),
                search: $request->query('q'),
                perPage: $request->integer('per_page') ?: null,
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد تصنيف بهذا الاسم.',
                'error' => 'category_not_found',
            ], 404);
        }

        /** @var LengthAwarePaginator $items */
        $items = $payload['items'];

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'items' => StoryListingCardResource::collection($items->items()),
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
     * Single story detail page.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $payload = $this->stories->show($uuid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد قصة بهذا المعرّف.',
                'error' => 'story_not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'story' => new StoryResource($payload['story']),
                'related' => [
                    'title' => $payload['related']['title'],
                    'subtitle' => $payload['related']['subtitle'],
                    'view_all' => $payload['related']['view_all'],
                    'items' => StoryListingCardResource::collection($payload['related']['items']),
                ],
            ],
        ]);
    }
}
