<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Collaborate\StoreCollaborationCreatorRequest;
use App\Http\Requests\Api\Collaborate\StoreCollaborationOtherRequest;
use App\Http\Requests\Api\Collaborate\StoreCollaborationPartnershipRequest;
use App\Http\Requests\Api\Collaborate\StoreCollaborationSponsorshipRequest;
use App\Http\Resources\CollaborationJoinRequestApiResource;
use App\Http\Resources\CollaborationTypeResource;
use App\Services\CollaborationPageService;
use Illuminate\Http\JsonResponse;

class CollaborationController extends Controller
{
    public function __construct(
        protected CollaborationPageService $collaboration,
    ) {}

    /**
     * Collaborate page: hero + collaboration type cards.
     */
    public function show(): JsonResponse
    {
        $payload = $this->collaboration->page();

        return response()->json([
            'data' => [
                'hero' => $payload['hero'],
                'types' => CollaborationTypeResource::collection($payload['types']),
            ],
        ]);
    }

    /**
     * Sponsorship / funding form wizard config.
     */
    public function sponsorshipForm(): JsonResponse
    {
        return response()->json([
            'data' => $this->collaboration->sponsorshipForm(),
        ]);
    }

    /**
     * Submit sponsorship / funding request (multipart when attachment is included).
     */
    public function submitSponsorship(StoreCollaborationSponsorshipRequest $request): JsonResponse
    {
        $joinRequest = $this->collaboration->submitSponsorship(
            $request->validated(),
            $request->file('attachment'),
        );

        return response()->json([
            'message' => 'تم استلام طلب التعاون، سنتواصل معك خلال 3–5 أيام عمل.',
            'data' => new CollaborationJoinRequestApiResource($joinRequest),
        ], 201);
    }

    /**
     * Strategic partnership form wizard config.
     */
    public function partnershipForm(): JsonResponse
    {
        return response()->json([
            'data' => $this->collaboration->partnershipForm(),
        ]);
    }

    /**
     * Submit strategic partnership request (multipart when attachment is included).
     */
    public function submitPartnership(StoreCollaborationPartnershipRequest $request): JsonResponse
    {
        $joinRequest = $this->collaboration->submitPartnership(
            $request->validated(),
            $request->file('attachment'),
        );

        return response()->json([
            'message' => 'تم استلام طلب التعاون، سنتواصل معك خلال 3–5 أيام عمل.',
            'data' => new CollaborationJoinRequestApiResource($joinRequest),
        ], 201);
    }

    /**
     * Creator collaborate form wizard config.
     */
    public function creatorForm(): JsonResponse
    {
        return response()->json([
            'data' => $this->collaboration->creatorForm(),
        ]);
    }

    /**
     * Submit creator collaborate request (multipart when intro video / attachment is included).
     */
    public function submitCreator(StoreCollaborationCreatorRequest $request): JsonResponse
    {
        $joinRequest = $this->collaboration->submitCreator(
            $request->validated(),
            $request->file('attachment') ?? $request->file('intro_video'),
        );

        return response()->json([
            'message' => 'تم استلام طلب التعاون، سنتواصل معك خلال 3–5 أيام عمل.',
            'data' => new CollaborationJoinRequestApiResource($joinRequest),
        ], 201);
    }

    /**
     * Other collaboration form wizard config.
     */
    public function otherForm(): JsonResponse
    {
        return response()->json([
            'data' => $this->collaboration->otherForm(),
        ]);
    }

    /**
     * Submit other collaboration request (multipart when attachment is included).
     */
    public function submitOther(StoreCollaborationOtherRequest $request): JsonResponse
    {
        $joinRequest = $this->collaboration->submitOther(
            $request->validated(),
            $request->file('attachment'),
        );

        return response()->json([
            'message' => 'تم استلام طلب التعاون، سنتواصل معك خلال 3–5 أيام عمل.',
            'data' => new CollaborationJoinRequestApiResource($joinRequest),
        ], 201);
    }
}
