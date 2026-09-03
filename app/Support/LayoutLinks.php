<?php

namespace App\Support;

/**
 * Fixed page-key → URL maps for platform + incubator chrome.
 * Admin edits labels/visibility only; the front owns routing via these keys.
 */
class LayoutLinks
{
    /** Keys excluded from the public navbar (legacy / not shown). */
    public const NAV_EXCLUDED_KEYS = ['courses'];

    /** Keys rendered as the top-bar support CTA (not in the main nav row). */
    public const NAV_TOPBAR_KEYS = ['support'];

    /** Keys rendered beside the logo / left of the main nav (often external). */
    public const NAV_SECONDARY_KEYS = ['incubator', 'media'];

    /**
     * Fixed page keys → site URLs (not editable from the dashboard).
     *
     * @var array<string, string>
     */
    public const PAGE_PATHS = [
        'home' => '/',
        'about' => '/about',
        'content' => '/content',
        'courses' => '/courses',
        'team' => '/team',
        'creators' => '/creators',
        'support' => '/support',
        'incubator' => '/incubator',
        'media' => '/media',
        'backstage' => '/backstage',
        'media_kit' => '/media-kit',
        'blog' => '/blog',
        'faq' => '/faq',
    ];

    /**
     * Blade / Laravel route targets for the same keys.
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

    /** Keys for incubator-site navbar (hash anchors on /incubator). */
    public const INCUBATOR_PAGE_PATHS = [
        'about' => '/incubator#about',
        'courses' => '/incubator#courses',
        'workshops' => '/incubator#workshops',
        'platform' => '/',
        'join' => '/incubator#join',
        'support_students' => '/support',
        'home' => '/',
        'team' => '/team',
        'creators' => '/creators',
        'content' => '/content',
        'incubator' => '/incubator',
        'media' => '/media',
    ];

    public static function pathForKey(?string $key): string
    {
        $key = trim((string) $key);

        return self::PAGE_PATHS[$key] ?? '#';
    }

    /** Resolve incubator nav key → hash path (falls back to main PAGE_PATHS). */
    public static function incubatorPathForKey(?string $key): string
    {
        $key = trim((string) $key);

        return self::INCUBATOR_PAGE_PATHS[$key] ?? self::pathForKey($key);
    }

    /**
     * Prefer explicit item url when absolute/path; otherwise map by key.
     *
     * @param  array<string, mixed>  $item
     */
    public static function incubatorPathForItem(array $item): string
    {
        $url = trim((string) ($item['url'] ?? ''));

        if ($url !== '' && $url !== '#') {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '/')) {
                return $url;
            }

            return '/'.ltrim($url, '/');
        }

        return self::incubatorPathForKey($item['key'] ?? null);
    }

    public static function pathForItem(array $item): string
    {
        $url = trim((string) ($item['url'] ?? ''));

        if ($url !== '' && $url !== '#') {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '/')) {
                return $url;
            }

            return '/'.ltrim($url, '/');
        }

        return self::pathForKey($item['key'] ?? null);
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
