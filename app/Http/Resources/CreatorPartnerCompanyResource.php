<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorPartnerCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'logo_url' => $this->logo_url,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
            'creators' => CreatorCardResource::collection($this->whenLoaded('creators')),
        ];
    }
}
