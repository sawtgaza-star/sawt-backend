<?php

namespace App\Support;

class LayoutLinks
{
    /**
     * Fixed page keys → site URLs (not editable from the dashboard).
     *
     * @var array<string, string>
     */
    public const PAGE_URLS = [
        'home' => '/',
        'about' => 'about',
        'content' => 'content',
        'courses' => 'courses.index',
        'team' => '#',
        'creators' => '#',
        'support' => 'donate',
        'incubator' => '#',
        'media' => '#',
        'backstage' => '#',
        'media_kit' => '#',
        'blog' => '#',
        'faq' => '#',
    ];

    public static function hrefForKey(?string $key): string
    {
        $key = trim((string) $key);
        $target = self::PAGE_URLS[$key] ?? '#';

        if ($target === '/' || $target === '') {
            return url('/');
        }

        if ($target === '#') {
            return '#';
        }

        if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://') || str_starts_with($target, '/')) {
            return $target === '/' ? url('/') : (str_starts_with($target, '/') ? url($target) : $target);
        }

        try {
            return route($target);
        } catch (\Throwable) {
            return '#';
        }
    }

    /**
     * Prefer a custom URL when set (footer quick links); otherwise fall back to key mapping.
     */
    public static function hrefForItem(array $item): string
    {
        $url = trim((string) ($item['url'] ?? ''));

        if ($url !== '' && $url !== '#') {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
                return $url;
            }

            if (str_starts_with($url, '/')) {
                return url($url);
            }

            return $url;
        }

        return self::hrefForKey($item['key'] ?? null);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function visible(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => ($item['is_visible'] ?? true) !== false)
            ->values()
            ->all();
    }
}
