<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public API shape after submitting a media consultation request.
 *
 * @mixin \App\Models\MediaConsultationRequest
 */
class MediaConsultationRequestApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'service' => [
                'slug' => $this->service_slug,
                'title' => $this->service_title,
            ],
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
