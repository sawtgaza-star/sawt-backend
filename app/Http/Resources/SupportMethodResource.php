<?php

namespace App\Http\Resources;

use App\Models\SupportMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportMethod
 */
class SupportMethodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'category' => $this->category,
            'provider' => $this->provider,
            'name' => $this->localized('name'),
            'description' => $this->localized('description'),
            'instructions' => $this->localized('instructions'),
            'logo_url' => $this->logo_url,
            'qr_image_url' => $this->qr_image_url,
            'account' => [
                'identifier' => $this->account_identifier,
                'holder' => $this->account_holder,
                'network' => $this->network,
                'currency' => $this->currency,
            ],
            'fields' => $this->detailFields(),
            'requires_proof' => $this->requires_proof,
            'is_paypal' => $this->isPayPal(),
            'sort_order' => $this->sort_order,
        ];
    }

    /**
     * الحقول المترجَمة الفارغة ترجع [] من Spatie — نثبّت الشكل على {ar, en}
     * حتى لا يتغيّر نوع الحقل على الفرونت بين وسيلة وأخرى.
     *
     * @return array{ar: string, en: string}
     */
    protected function localized(string $attribute): array
    {
        $values = $this->getTranslations($attribute);

        return [
            'ar' => (string) ($values['ar'] ?? ''),
            'en' => (string) ($values['en'] ?? $values['ar'] ?? ''),
        ];
    }
}
