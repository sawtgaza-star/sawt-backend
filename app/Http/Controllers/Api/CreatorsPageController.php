<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Creators\StoreCreatorJoinRequest;
use App\Http\Resources\CreatorCardResource;
use App\Http\Resources\CreatorFaqResource;
use App\Http\Resources\CreatorJoinRequestResource;
use App\Http\Resources\CreatorPartnerCompanyResource;
use App\Services\CreatorsPageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorsPageController extends Controller
{
    public function __construct(
        protected CreatorsPageService $creatorsPage,
    ) {}

    public function index(): JsonResponse
    {
        $payload = $this->creatorsPage->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'grid' => [
                    'title' => $payload['grid']['title'],
                    'subtitle' => $payload['grid']['subtitle'],
                    'browse_label' => $payload['grid']['browse_label'],
                    'creators' => CreatorCardResource::collection($payload['grid']['creators']),
                ],
                'stats' => $payload['stats'],
                'join' => $payload['join'],
                'partners' => [
                    'title' => $payload['partners']['title'],
                    'description' => $payload['partners']['description'],
                    'companies' => CreatorPartnerCompanyResource::collection($payload['partners']['companies']),
                ],
                'collaboration' => $payload['collaboration'],
                'faq' => [
                    'title' => $payload['faq']['title'],
                    'subtitle' => $payload['faq']['subtitle'],
                    'image_url' => $payload['faq']['image_url'],
                    'items' => CreatorFaqResource::collection($payload['faq']['items']),
                ],
            ],
        ]);
    }

    /**
     * View-all listing: paginated creator cards.
     * Query: ?page=1&per_page=10&q=محمود
     */
    public function all(Request $request): JsonResponse
    {
        $payload = $this->creatorsPage->all(
            search: $request->query('q'),
            perPage: $request->integer('per_page') ?: null,
        );

        $paginator = $payload['creators'];

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'labels' => $payload['labels'],
                'creators' => CreatorCardResource::collection($paginator->items()),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function join(StoreCreatorJoinRequest $request): JsonResponse
    {
        $joinRequest = $this->creatorsPage->submitJoin($request->validated());

        return response()->json([
            'message' => 'تم استلام طلب الانضمام، سنتواصل معك قريباً.',
            'data' => new CreatorJoinRequestResource($joinRequest),
        ], 201);
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $payload = $this->creatorsPage->creator($uuid);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'لا يوجد صانع محتوى بهذا المعرّف.',
                'error' => 'creator_not_found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'creator' => new CreatorCardResource($payload['creator']),
                'labels' => $payload['labels'],
            ],
        ]);
    }
}
