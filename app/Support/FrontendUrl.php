<?php

namespace App\Support;

/**
 * Builds absolute URLs for the public website (Next/front), not the Laravel API host.
 * Set FRONTEND_URL in .env (e.g. https://sawtgaza.com) so emails work in production.
 */
class FrontendUrl
{
    /**
     * Root of the public site (no trailing slash).
     */
    public static function root(): string
    {
        $url = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $url !== '' ? $url : (string) config('app.url');
    }

    /**
     * Absolute URL for a front path (leading slash optional).
     */
    public static function to(string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');
        if ($path === '/') {
            return static::root();
        }

        return static::root().$path;
    }

    /**
     * Course detail page on the incubator front.
     * Override pattern with FRONTEND_COURSE_PATH (must include {slug}).
     */
    public static function course(string $slug): string
    {
        $pattern = (string) config('app.frontend_course_path', '/courses/{slug}');
        $path = str_replace('{slug}', rawurlencode($slug), $pattern);

        return static::to($path);
    }

    /** Incubator landing (browse other courses). */
    public static function incubator(): string
    {
        return static::to((string) config('app.frontend_incubator_path', '/incubator'));
    }

    /** Website login page. */
    public static function login(): string
    {
        return static::to((string) config('app.frontend_login_path', '/login'));
    }
}
