<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * لقطة شاشة لإثبات التحويل. تُخزَّن على القرص الخاص (storage/app/private)
 * لأنها تحتوي بيانات مالية للمتبرع — الوصول عبر رابط موقّت فقط.
 */
class SupportRequestProof extends Model
{
    use HasUuid;

    protected $fillable = [
        'support_request_id', 'path', 'disk', 'original_name', 'mime_type', 'size',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    /** معرّف كامل — رابط تحميل الإثبات لا يجوز أن يكون قابلاً للتخمين. */
    public static function generateUniqueShortUuid(int $length = 36): string
    {
        do {
            $uuid = (string) Str::uuid();
        } while (static::query()->where('uuid', $uuid)->exists());

        return $uuid;
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SupportRequest::class, 'support_request_id');
    }

    /**
     * رابط مؤقّت للمعاينة (الافتراضي 30 دقيقة).
     * القرص المحلي لا يدعم temporaryUrl فنرجع مسار التحميل المحمي.
     */
    public function temporaryUrl(int $minutes = 30): ?string
    {
        $disk = Storage::disk($this->disk ?: 'local');

        if (! $disk->exists($this->path)) {
            return null;
        }

        try {
            return $disk->temporaryUrl($this->path, now()->addMinutes($minutes));
        } catch (\RuntimeException) {
            return route('support.proofs.download', ['uuid' => $this->uuid]);
        }
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk ?: 'local')->exists($this->path);
    }
}
