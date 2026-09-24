<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One company tab in «أبرز التعاونات» — own caption/author; video is section-level reel.
 */
class CreatorCollaborationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;

        return [
            'uuid' => $this->uuid,
            'sort_order' => (int) ($pivot?->sort_order ?? $this->sort_order ?? 0),
            'company' => [
                'uuid' => $this->uuid,
                'name' => $this->getTranslations('name'),
                'category' => $this->getTranslations('category'),
                'logo_url' => $this->logo_url,
                'url' => $this->url,
            ],
            'caption' => [
                'ar' => (string) ($pivot?->quote_ar ?? ''),
                'en' => (string) ($pivot?->quote_en ?? ''),
            ],
            'rating' => (int) ($pivot?->rating ?? 5),
            'author' => [
                'name' => $pivot?->author_name,
                'role' => [
                    'ar' => (string) ($pivot?->author_role_ar ?? ''),
                    'en' => (string) ($pivot?->author_role_en ?? ''),
                ],
                'photo_url' => MediaUrl::make($pivot?->author_photo),
            ],
        ];
    }
}
