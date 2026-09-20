<?php

namespace App\Support;

/**
 * Strip / repair invalid UTF-8 so json_encode never throws
 * "Malformed UTF-8 characters, possibly incorrectly encoded".
 */
class Utf8
{
    /**
     * Recursively clean strings in arrays / scalars for safe JSON output.
     */
    public static function clean(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::cleanString($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::clean($item);
            }

            return $value;
        }

        return $value;
    }

    /**
     * Drop bytes that are not valid UTF-8; keep Arabic/English intact.
     */
    public static function cleanString(string $value): string
    {
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Remove invalid sequences; fallback if iconv unavailable.
        if (function_exists('iconv')) {
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if ($cleaned !== false) {
                return $cleaned;
            }
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
