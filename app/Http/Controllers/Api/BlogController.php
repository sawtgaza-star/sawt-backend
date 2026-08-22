<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogListingCardResource;
use App\Http\Resources\BlogResource;
use App\Services\BlogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected BlogService $blogs,
    ) {}

    /**
     * Blog listing page data.
     *
     * Query: ?page=1&per_page=12&category=slug&q=search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payload = $this->blogs->listingPage(
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
                'items' => BlogListingCardResource::collection($items->items()),
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
     * Single blog article (detail page).
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $payload = $this->blogs->show($uuid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد خبر بهذا المعرّف.',
                'error' => 'blog_not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'blog' => new BlogResource($payload['blog']),
                'related' => BlogListingCardResource::collection($payload['related']),
            ],
        ]);
    }
}
