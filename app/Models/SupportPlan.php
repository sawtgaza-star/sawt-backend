<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * باقة دعم جاهزة: مبلغ + دورية (لمرة واحدة / شهري / سنوي).
 * الدوريات تُربط بخطة PayPal عبر paypal_plan_id.
 */
class SupportPlan extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['label', 'description'];

    protected $fillable = [
        'interval', 'amount', 'currency', 'label', 'description',
        'paypal_plan_id', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SupportSubscription::class);
    }

    public function isRecurring(): bool
    {
        return in_array($this->interval, ['monthly', 'yearly'], true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('amount');
    }

    public function scopeInterval($query, string $interval)
    {
        return $query->where('interval', $interval);
    }
}
