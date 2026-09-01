<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogListingCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'cover_image' => MediaUrl::make($this->cover_image),
            'excerpt' => $this->getTranslations('excerpt'),
            'publish_date' => optional($this->published_at)?->toIso8601String(),
        ];
    }
}
