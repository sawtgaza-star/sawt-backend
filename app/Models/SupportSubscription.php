<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * اشتراك دعم دوري عبر PayPal Billing (شهري / سنوي).
 * كل دورة تحصيل ناجحة تُسجَّل كـ Payment مرتبط بهذا الاشتراك.
 */
class SupportSubscription extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id', 'support_plan_id', 'gateway', 'gateway_subscription_id', 'gateway_plan_id',
        'interval', 'amount', 'currency', 'status',
        'subscriber_name', 'subscriber_email',
        'started_at', 'next_billing_at', 'cancelled_at', 'total_paid', 'cycles_completed', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'cycles_completed' => 'integer',
            'started_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /** الضيوف يديرون اشتراكهم بالـ uuid وحده، فيجب ألا يكون قابلاً للتخمين. */
    public static function generateUniqueShortUuid(int $length = 36): string
    {
        do {
            $uuid = (string) Str::uuid();
        } while (static::query()->where('uuid', $uuid)->exists());

        return $uuid;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SupportPlan::class, 'support_plan_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['approval_pending', 'active', 'suspended'], true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
