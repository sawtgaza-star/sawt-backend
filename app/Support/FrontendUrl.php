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

    /** Support page PayPal return (thank-you) page. */
    public static function supportReturn(): string
    {
        return static::to((string) config('app.frontend_support_return_path', '/support/thank-you'));
    }

    /** Support page PayPal cancel page. */
    public static function supportCancel(): string
    {
        return static::to((string) config('app.frontend_support_cancel_path', '/support'));
    }

    /**
     * Keep a client-supplied redirect only when it points at our own frontend host
     * (avoids turning PayPal into an open redirect); otherwise use $fallback.
     */
    public static function sameHostOr(?string $url, string $fallback): string
    {
        if (! filled($url)) {
            return $fallback;
        }

        $host = parse_url((string) $url, PHP_URL_HOST);
        $scheme = parse_url((string) $url, PHP_URL_SCHEME);

        return $host
            && in_array($scheme, ['http', 'https'], true)
            && strcasecmp($host, (string) parse_url(static::root(), PHP_URL_HOST)) === 0
                ? (string) $url
                : $fallback;
    }

    /** Website login page. */
    public static function login(): string
    {
        return static::to((string) config('app.frontend_login_path', '/login'));
    }
}
