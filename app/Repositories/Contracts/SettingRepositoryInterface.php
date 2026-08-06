<?php

namespace App\Repositories\Contracts;

interface SettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @return array{ar: string, en: string}
     */
    public function i18n(string $baseKey, string $fallbackAr = '', string $fallbackEn = ''): array;
}
