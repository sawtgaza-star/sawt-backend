<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $seconds = (int) ($this->duration_seconds ?? 0);

        return [
            'uuid' => $this->uuid,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'slug' => $this->slug,
            'cover_url' => MediaUrl::make($this->cover_url),
            'video_url' => $this->video_url,
            'duration_seconds' => $seconds ?: null,
            'duration' => $seconds > 0 ? $this->formatDuration($seconds) : null,
            'views' => (int) ($this->play_count ?? 0),
            'likes' => (int) ($this->like_count ?? 0),
            'comments_count' => (int) ($this->comment_count ?? 0),
            'is_featured' => (bool) $this->is_featured,
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'collaborator' => $this->whenLoaded('creator', function () {
                if (! $this->creator) {
                    return null;
                }

                return [
                    'uuid' => $this->creator->uuid,
                    'username' => $this->creator->username,
                    'name' => $this->creator->user?->name,
                    'avatar_url' => $this->creator->avatar_url,
                ];
            }),
            'category' => $this->whenLoaded('category', function () {
                if (! $this->category) {
                    return null;
                }

                return [
                    'uuid' => $this->category->uuid,
                    'name' => $this->category->getTranslations('name'),
                    'slug' => $this->category->slug,
                ];
            }),
        ];
    }

    protected function formatDuration(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return sprintf('%d:%02d', $m, $s);
    }
}
