<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'image_url' => MediaUrl::make($this->image),
            'sort_order' => (int) ($this->sort_order ?? 0),
        ];
    }
}
