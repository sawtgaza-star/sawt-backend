<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full creator profile for GET /pages/creators/{uuid} (detail page).
 * Listing cards still use HomeCreatorCardResource / CreatorCardResource.
 */
class CreatorDetailResource extends JsonResource
{
    /**
     * Extra keys merged from CreatorsPageService (stats, featured, etc.).
     *
     * @param  array<string, mixed>  $extras
     */
    public function __construct($resource, protected array $extras = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->user?->name ?? $this->username,
            'role' => $this->getTranslations('role'),
            'bio' => $this->getTranslations('bio'),
            'avatar_url' => $this->avatar_url,
            'is_verified' => (bool) $this->is_verified,
            'instagram_username' => $this->instagramUsername(),
            'socials' => $this->whenLoaded('socials', fn () => $this->socials->map(fn ($s) => [
                'platform' => $s->platform,
                'url' => $s->url,
                'followers_count' => (int) ($s->followers_count ?? 0),
            ])->values()),
            'stats' => $this->extras['stats'] ?? [
                'views' => (int) ($this->views_count ?? 0),
                'followers' => (int) ($this->followers_count ?? 0),
                'videos' => 0,
            ],
        ];
    }
}
