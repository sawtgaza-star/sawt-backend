<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\SupportOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * جلسة ويزارد الدعم كاملة: اختيار الوسيلة ← إثبات التحويل ← دعم الفريق ← وسيلة التواصل.
 * يبقى بحالة draft لحد ما تكتمل الخطوة الأخيرة، وبعدها ينتظر مراجعة الإدارة.
 */
class SupportRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id', 'support_method_id', 'support_plan_id', 'campaign_id', 'donation_id',
        'category', 'interval', 'amount', 'currency',
        'major_id', 'team_member_id', 'message', 'is_anonymous',
        'donor_name', 'donor_email', 'donor_phone', 'contact_preference', 'contact_value', 'subscribe_newsletter',
        'transfer_reference', 'transfer_date', 'sender_name',
        'current_step', 'status', 'submitted_at',
        'reviewed_by', 'reviewed_at', 'admin_note', 'rejection_reason', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'subscribe_newsletter' => 'boolean',
            'transfer_date' => 'date',
            'current_step' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(SupportMethod::class, 'support_method_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SupportPlan::class, 'support_plan_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(SupportRequestProof::class);
    }

    /**
     * الضيوف يستكملون الويزارد بالـ uuid وحده، فنستخدم UUID v4 كامل
     * بدل المعرّف القصير (5 أحرف) المستخدم بالموارد العامة — حتى لا يُخمَّن.
     */
    public static function generateUniqueShortUuid(int $length = 36): string
    {
        do {
            $uuid = (string) Str::uuid();
        } while (static::query()->where('uuid', $uuid)->exists());

        return $uuid;
    }

    /** الخطوة التالية بالويزارد، أو null لو خلص. */
    public function nextStep(): ?string
    {
        return SupportOptions::STEPS[$this->current_step] ?? null;
    }

    public function stepName(): string
    {
        return SupportOptions::STEPS[$this->current_step - 1] ?? 'method';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['pending', 'under_review', 'approved', 'rejected'], true);
    }

    /** هل الوسيلة المختارة تتطلب إثبات تحويل. */
    public function needsProof(): bool
    {
        return (bool) ($this->method?->requires_proof ?? true);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'under_review']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', '!=', 'draft');
    }
}
