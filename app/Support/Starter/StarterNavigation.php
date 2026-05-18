<?php

namespace App\Support\Starter;

class StarterNavigation
{
    public static function authHost(): string
    {
        return 'auth.'.config('app.domain');
    }

    public static function authLoginUrl(?string $redirect = null): string
    {
        $url = request()->getScheme().'://'.self::authHost().'/login';

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
