<?php

namespace App\Support\Starter;

class StarterNavigation
{
    public static function rootHost(): string
    {
        return (string) config('app.domain');
    }

    public static function authUrl(string $path = ''): string
    {
        $path = trim($path, '/');

        return request()->getScheme().'://'.self::rootHost().'/auth'.($path === '' ? '' : '/'.$path);
    }

    public static function authLoginUrl(?string $redirect = null): string
    {
        $url = self::authUrl('login');

        return $redirect ? $url.'?'.http_build_query(['redirect' => $redirect]) : $url;
    }

    public static function isSafeRedirect(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $domain = config('app.domain');

        return $host === $domain || str_ends_with((string) $host, '.'.$domain);
    }
}
