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
        static::saved(fn (Setting $setting) => Cache::forget(static::cacheKey($setting->key)));
        static::deleted(fn (Setting $setting) => Cache::forget(static::cacheKey($setting->key)));
    }

    public static function cacheKey(string $key): string
    {
        // v2: لا نخزّن القيم الافتراضية في الكاش (كانت تسبب ظهور بيانات ثابتة في الـ API)
        return "setting.v2.{$key}";
    }

    /**
     * قراءة إعداد من الكاش أو الـ DB.
     * القيمة الافتراضية تُستخدم فقط عند غياب السجل — ولا تُخزَّن في الكاش.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = static::cacheKey($key);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'number' => (float) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };

        Cache::forever($cacheKey, $value);

        return $value;
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
