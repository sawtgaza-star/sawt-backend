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
            'major' => $this->getTranslations('role'),
        ];
    }
}
