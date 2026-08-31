<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaborationTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'icon_url' => $this->icon_url,
            'sort_order' => (int) ($this->sort_order ?? 0),
        ];
    }
}
