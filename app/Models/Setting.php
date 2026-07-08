<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    protected static function booted(): void
    {
        // أي حفظ أو حذف بيمسح الكاش تلقائياً — هيك الأدمن يعدل والتغيير يبان فوراً
        static::saved(fn (Setting $setting) => Cache::forget("setting.{$setting->key}"));
        static::deleted(fn (Setting $setting) => Cache::forget("setting.{$setting->key}"));
    }

    /**
     * قراءة إعداد — من الكاش أول شي، ولو مو موجود بتجيبه من الـ DB وتخزنه بالكاش للأبد.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'number' => (float) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * كتابة/تحديث إعداد — بيمسح الكاش تلقائياً بفضل الـ booted() فوق.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $type === 'json' ? json_encode($value) : $value,
                'group' => $group,
                'type' => $type,
            ]
        );
    }
}
