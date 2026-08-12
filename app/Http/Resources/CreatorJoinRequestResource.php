<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorJoinRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'email' => $this->email,
            'content_types' => $this->content_types ?? [],
            'followers_count' => $this->followers_count,
            'content_bio' => $this->content_bio,
            'socials' => $this->socials ?? [],
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
