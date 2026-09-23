<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Homepage team cards — only the mic portrait (no regular photo).
 */
class HomeTeamCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'mic_image' => $this->mic_photo_url,
            'name' => $this->getTranslations('name'),
            'role' => $this->getTranslations('role'),
            'major' => $this->whenLoaded('major', fn () => [
                'uuid' => $this->major->uuid,
                'name' => $this->major->getTranslations('name'),
                'slug' => $this->major->slug,
            ]),
        ];
    }
}
