<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->getTranslations('title'),
            'excerpt' => $this->getTranslations('excerpt'),
            'cover_url' => MediaUrl::make($this->cover_image),
            'read_time_minutes' => $this->readTimeMinutes(),
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'categories' => $this->formattedCategories(),
        ];
    }
}
