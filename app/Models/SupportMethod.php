<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * وسيلة دعم واحدة (بنك، محفظة، عملة رقمية…) — كلها تتعرّف من لوحة التحكم.
 */
class SupportMethod extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    /** @var list<string> */
    protected array $storedUploads = ['logo', 'qr_image'];

    public array $translatable = ['name', 'description', 'instructions'];

    protected $fillable = [
        'category', 'provider', 'name', 'description', 'instructions',
        'logo', 'qr_image', 'account_identifier', 'account_holder',
        'network', 'currency', 'fields', 'requires_proof', 'is_active', 'sort_order',
    ];

    protected $appends = ['logo_url', 'qr_image_url'];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'requires_proof' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SupportRequest::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->logo);
    }

    public function getQrImageUrlAttribute(): ?string
    {
        return MediaUrl::make($this->qr_image);
    }

    /**
     * الحقول الحرة بشكل جاهز للفرونت — مع تجاهل الصفوف الفارغة.
     *
     * @return array<int, array{label: array{ar: string, en: string}, value: string, is_copyable: bool}>
     */
    public function detailFields(): array
    {
        return collect($this->fields ?? [])
            ->filter(fn ($row) => filled($row['value'] ?? null))
            ->map(fn ($row) => [
                'label' => [
                    'ar' => (string) ($row['label_ar'] ?? ''),
                    'en' => (string) ($row['label_en'] ?? $row['label_ar'] ?? ''),
                ],
                'value' => (string) $row['value'],
                'is_copyable' => (bool) ($row['is_copyable'] ?? true),
            ])
            ->values()
            ->all();
    }

    public function isPayPal(): bool
    {
        return $this->provider === 'paypal';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
