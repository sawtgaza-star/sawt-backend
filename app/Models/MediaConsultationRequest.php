<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Landing «احجز استشارتك» form row — pending until admin approves/rejects.
 */
class MediaConsultationRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_code',
        'media_service_id',
        'service_slug',
        'service_title',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MediaServiceItem::class, 'media_service_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** Full phone string for display (country code + number). */
    public function fullPhone(): string
    {
        $code = trim((string) $this->country_code);
        $phone = trim((string) $this->phone);

        if ($code === '') {
            return $phone;
        }

        return $code.' '.$phone;
    }
}
