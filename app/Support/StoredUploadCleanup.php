<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StoredUploadCleanup
{
    /**
     * @return list<string>
     */
    public static function collectPaths(mixed $data): array
    {
        if (is_string($data)) {
            return self::isStoredPath($data) ? [self::normalizePath($data)] : [];
        }

        if (! is_array($data)) {
            return [];
        }

        $paths = [];

        foreach ($data as $value) {
            $paths = array_merge($paths, self::collectPaths($value));
        }

        return array_values(array_unique($paths));
    }

    public static function isStoredPath(string $path): bool
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        if (str_starts_with($path, '/assets/') || str_starts_with($path, 'assets/')) {
            return false;
        }

        if (str_contains($path, '..')) {
            return false;
        }

        return str_contains($path, '/');
    }

    public static function normalizePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return $path;
    }

    /**
     * @param  list<string>  $paths
     */
    public static function deletePaths(array $paths): void
    {
        $disk = Storage::disk('public');

        foreach ($paths as $path) {
            $path = self::normalizePath($path);

            if ($path === '' || ! self::isStoredPath($path)) {
                continue;
            }

            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    public static function pruneReplaced(mixed $old, mixed $new): void
    {
        $removed = array_diff(self::collectPaths($old), self::collectPaths($new));

        if ($removed !== []) {
            self::deletePaths(array_values($removed));
        }
    }
}
