<?php

namespace App\Http\Resources;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->getTranslations('title'),
            'excerpt' => $this->getTranslations('excerpt'),
            'cover_url' => MediaUrl::make($this->cover_image),
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
            'images' => BlogImageResource::collection($this->whenLoaded('images')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function breadcrumb(): array
    {
        $settings = app(SettingRepositoryInterface::class);

        return [
            'home' => $settings->i18n('blog_breadcrumb_home', 'الرئيسية', 'Home'),
            'news' => $settings->i18n('blog_breadcrumb_news', 'آخر الأخبار', 'Latest News'),
            'current' => $this->getTranslations('title'),
        ];
    }
}
