<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorFaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'question' => $this->getTranslations('question'),
            'answer' => $this->getTranslations('answer'),
            'sort_order' => $this->sort_order,
        ];
    }
}
