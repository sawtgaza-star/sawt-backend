<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'username' => $this->username,
            'name' => $this->user?->name,
            'role' => $this->getTranslations('role'),
            'bio' => $this->getTranslations('bio'),
            'avatar_url' => $this->avatar_url,
            'followers_count' => $this->followers_count,
            'is_verified' => $this->is_verified,
            'sort_order' => $this->sort_order,
            'socials' => $this->whenLoaded('socials', fn () => $this->socials->map(fn ($s) => [
                'platform' => $s->platform,
                'url' => $s->url,
            ])->values()),
        ];
    }
}
