<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MajorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'members_count' => (int) ($this->members_count ?? 0),
        ];
    }
}
