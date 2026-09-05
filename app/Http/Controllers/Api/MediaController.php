<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Media\StoreMediaConsultationRequest;
use App\Http\Resources\MediaConsultationRequestApiResource;
use App\Services\MediaConsultationRequestService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

/**
 * Public Sawt Media APIs — landing, contact, services, and works.
 * Chrome (navbar/footer): GET /api/v1/layout/media
 */
class MediaController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected MediaConsultationRequestService $consultations,
    ) {}

    /**
     * Assemble media landing JSON (hero → … → faq). No navbar/footer here.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => $this->media->page(),
        ]);
    }

    /**
     * Submit «احجز استشارتك» form — POST /api/v1/pages/media/consultation
     */
    public function storeConsultation(StoreMediaConsultationRequest $request): JsonResponse
    {
        $row = $this->consultations->submit($request->validated());

        return response()->json([
            'message' => 'تم استلام طلب الاستشارة، سنتواصل معك قريباً.',
            'data' => new MediaConsultationRequestApiResource($row),
        ], 201);
    }

    /**
     * Contact / «ابدأ مشروعك» page — GET /api/v1/pages/media/contact.
     */
    public function contact(): JsonResponse
    {
        return response()->json([
            'data' => $this->media->contactPage(),
        ]);
    }

    /**
     * Works list — GET /api/v1/pages/media/works
     * Archive page: hero + filters (service / tag) + all active works.
     */
    public function works(): JsonResponse
    {
        return response()->json([
            'data' => $this->media->worksIndex(),
        ]);
    }

    /**
     * Work detail — GET /api/v1/pages/media/works/{slugOrUuid}
     */
    public function work(string $slug): JsonResponse
    {
        $data = $this->media->workBySlug($slug);

        if ($data === null) {
            return response()->json([
                'message' => 'العمل غير موجود.',
                'error' => 'media_work_not_found',
            ], 404);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Services list — GET /api/v1/pages/media/services
     */
    public function services(): JsonResponse
    {
        return response()->json([
            'data' => $this->media->servicesIndex(),
        ]);
    }

    /**
     * Lean services for dropdowns — GET /api/v1/pages/media/services/options
     * Returns id, uuid, slug, name only.
     */
    public function servicesOptions(): JsonResponse
    {
        return response()->json([
            'data' => $this->media->servicesOptions(),
        ]);
    }

    /**
     * Single service detail — GET /api/v1/pages/media/services/{slugOrUuid}.
     */
    public function service(string $slug): JsonResponse
    {
        $data = $this->media->serviceBySlug($slug);

        if ($data === null) {
            return response()->json([
                'message' => 'الخدمة غير موجودة.',
                'error' => 'media_service_not_found',
            ], 404);
        }

        return response()->json(['data' => $data]);
    }
}
