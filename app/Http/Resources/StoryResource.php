<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTranslations('title'),
            'excerpt' => $this->getTranslations('excerpt'),
            'cover_url' => MediaUrl::make($this->cover_image),
            'hero_url' => MediaUrl::make($this->heroImagePath()),
            'breadcrumb' => $this->breadcrumb(),
            'categories' => $this->formattedCategories(),
            'author' => $this->getTranslations('author_name'),
            'read_time_minutes' => $this->readTimeMinutes(),
            'views' => (int) ($this->views_count ?? 0),
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'content' => $this->getTranslations('content'),
            'quote' => [
                'text' => $this->getTranslations('quote_text'),
                'author' => $this->getTranslations('quote_author'),
            ],
            'images' => StoryImageResource::collection($this->whenLoaded('images')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function breadcrumb(): array
    {
        return [
            'home' => ['ar' => 'الرئيسية', 'en' => 'Home'],
            'stories' => ['ar' => 'قصص النجاح', 'en' => 'Success Stories'],
            'current' => $this->getTranslations('title'),
        ];
    }
}
