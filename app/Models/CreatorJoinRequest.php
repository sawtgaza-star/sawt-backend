<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Services\CreatorJoinRequestService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorJoinRequest extends Model
{
    use HasUuid;

    public const PLATFORMS = ['instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok', 'telegram', 'other'];

    protected $fillable = [
        'full_name',
        'phone',
        'country_code',
        'email',
        'content_types',
        'followers_count',
        'content_bio',
        'socials',
        'notes',
        'status',
        'creator_id',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'content_types' => 'array',
            'socials' => 'array',
            'followers_count' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    protected static function booted(): void
    {
        static::deleting(function (CreatorJoinRequest $joinRequest) {
            app(CreatorJoinRequestService::class)->deleteLinkedProfiles($joinRequest);
        });
    }
}
