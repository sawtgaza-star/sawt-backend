<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamListingCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'image' => $this->photo_url,
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
