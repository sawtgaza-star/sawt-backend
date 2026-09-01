<?php

namespace App\Support;

class MediaUrl
{
    public static function make(?string $path, ?string $fallback = null): ?string
    {
        if ($path === null || $path === '') {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return url('storage/'.ltrim($path, '/'));
    }
}
