<?php

namespace App\Models;

use App\Enums\CollaborationTypeKey;
use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationJoinRequest extends Model
{
    use HasUuid;

    /** @var list<string> */
    public const SPONSORSHIP_SUPPORT_TYPES = [
        'direct_financial',
        'in_kind',
        'marketing_media',
        'other',
    ];

    /** @var list<string> */
    public const PARTNERSHIP_TYPES = [
        'content_exchange',
        'advertising_sponsorship',
        'event_collaboration',
        'other',
    ];

    protected $fillable = [
        'type',
        'company_name',
        'email',
        'phone',
        'country_code',
        'website',
        'payload',
        'attachment',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected $appends = ['attachment_url'];

    protected function casts(): array
    {
        return [
            'type' => CollaborationTypeKey::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function collaborationType(): BelongsTo
    {
        return $this->belongsTo(CollaborationType::class, 'type', 'key');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return MediaUrl::make($this->attachment);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOfType($query, CollaborationTypeKey|string $type)
    {
        $value = $type instanceof CollaborationTypeKey ? $type->value : $type;

        return $query->where('type', $value);
    }

    public static function sponsorshipSupportTypeLabel(string $key): string
    {
        return match ($key) {
            'direct_financial' => 'تمويل مالي مباشر',
            'in_kind' => 'دعم عيني (معدات، مساحات…)',
            'marketing_media' => 'دعم تسويقي وإعلامي',
            'other' => 'أخرى',
            default => $key,
        };
    }

    public static function partnershipTypeLabel(string $key): string
    {
        return match ($key) {
            'content_exchange' => 'تبادل محتوى',
            'advertising_sponsorship' => 'رعاية إعلانية',
            'event_collaboration' => 'تعاون بفعاليات',
            'other' => 'أخرى',
            default => $key,
        };
    }
}
