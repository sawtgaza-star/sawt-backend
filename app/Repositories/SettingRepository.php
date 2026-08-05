<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    public function i18n(string $baseKey, string $fallbackAr = '', string $fallbackEn = ''): array
    {
        return [
            'ar' => (string) ($this->get("{$baseKey}_ar", $fallbackAr) ?? $fallbackAr),
            'en' => (string) ($this->get("{$baseKey}_en", $fallbackEn) ?? $fallbackEn),
        ];
    }
}
