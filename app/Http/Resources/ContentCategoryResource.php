<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'videos_count' => (int) ($this->videos_count ?? 0),
        ];
    }
}
