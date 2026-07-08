<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                $model->uuid = static::generateUniqueShortUuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function generateUniqueShortUuid(int $length = 5): string
    {
        do {
            $uuid = Str::lower(Str::random($length));
        } while (static::query()->where('uuid', $uuid)->exists());

        return $uuid;
    }
}
