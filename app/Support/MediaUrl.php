<?php

namespace App\Support;

use Filament\Tables\Columns\ImageColumn;

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

        return url('media/'.ltrim($path, '/'));
    }

    /**
     * Filament table thumbnail — serves via /media/ (Hostinger blocks /storage/).
     */
    public static function tableImageColumn(string $name, ?string $label = null): ImageColumn
    {
        $column = ImageColumn::make($name)
            ->getStateUsing(fn ($record): ?string => self::make($record->{$name} ?? null))
            ->checkFileExistence(false);

        if ($label !== null) {
            $column->label($label);
        }

        return $column;
    }
}
