<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaborationJoinRequestApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type?->value ?? $this->type,
            'company_name' => $this->company_name,
            'full_name' => $this->payload['full_name'] ?? $this->company_name,
            'name' => $this->payload['name'] ?? $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'website' => $this->website,
            'payload' => $this->payload ?? [],
            'attachment_url' => $this->attachment_url,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
