<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeCreatorCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'name' => $this->user?->name ?? $this->username,
            'role' => $this->getTranslations('role'),
            'avatar_url' => $this->avatar_url,
            'followers_count' => (int) ($this->followers_count ?? 0),
            'experience_excerpt' => $this->getTranslations('bio'),
        ];
    }
}
