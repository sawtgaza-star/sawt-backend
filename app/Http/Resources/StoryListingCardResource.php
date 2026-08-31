<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryListingCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $headline = $this->getTranslations('card_headline');
        if (blank($headline['ar'] ?? null) && blank($headline['en'] ?? null)) {
            $headline = $this->getTranslations('title');
        }

        return [
            'uuid' => $this->uuid,
            'id' => $this->id,
            'cover_image' => MediaUrl::make($this->cover_image),
            'badge' => $this->primaryBadge(),
            'headline' => $headline,
            'excerpt' => $this->getTranslations('excerpt'),
            'footer_title' => $this->getTranslations('card_footer_title'),
            'footer_subtitle' => $this->getTranslations('card_footer_subtitle'),
        ];
    }
}
