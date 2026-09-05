<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Locale-aware helpers for Filament admin (AR/EN switcher).
 * Prefer app locale, then fall back to the other language so cells never go blank.
 */
class LocaleText
{
    /**
     * Read a Spatie translatable attribute for the current UI locale.
     */
    public static function translation(?Model $model, string $field, ?string $fallbackLocale = null): string
    {
        if ($model === null || ! method_exists($model, 'getTranslation')) {
            return '';
        }

        $locale = app()->getLocale();
        $other = $locale === 'en' ? 'ar' : 'en';
        $fallback = $fallbackLocale ?: $other;

        $value = (string) $model->getTranslation($field, $locale, false);
        if ($value !== '') {
            return $value;
        }

        return (string) $model->getTranslation($field, $fallback, false);
    }

    /**
     * Common request status badge label (pending / approved / rejected).
     */
    public static function requestStatus(string $status): string
    {
        return match ($status) {
            'pending' => __('Pending review'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
            default => $status,
        };
    }

    /**
     * Pick bilingual repeater field (label_ar/label_en, title_ar/title_en, …) for UI locale.
     *
     * @param  array<string, mixed>  $state
     */
    public static function pick(array $state, string $base, ?string $fallbackKey = null): string
    {
        $locale = app()->getLocale();
        $other = $locale === 'en' ? 'ar' : 'en';

        $primary = trim((string) ($state["{$base}_{$locale}"] ?? ''));
        if ($primary !== '') {
            return $primary;
        }

        $secondary = trim((string) ($state["{$base}_{$other}"] ?? ''));
        if ($secondary !== '') {
            return $secondary;
        }

        return $fallbackKey !== null ? (string) __($fallbackKey) : '';
    }
}
