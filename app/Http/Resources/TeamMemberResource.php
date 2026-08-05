<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->getTranslations('name'),
            'role' => $this->getTranslations('role'),
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->getTranslations('bio'),
            'photo_url' => $this->photo_url,
            'socials' => $this->socialLinks(),
            'sort_order' => $this->sort_order,
            'major' => $this->whenLoaded('major', fn () => [
                'uuid' => $this->major->uuid,
                'name' => $this->major->getTranslations('name'),
                'slug' => $this->major->slug,
            ]),
        ];
    }
}
