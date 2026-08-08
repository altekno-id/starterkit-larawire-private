<?php

namespace Altekno\StarterKit\Support\Starter;

use RuntimeException;

class StarterTheme
{
    private const LAYOUTS = ['vertical', 'horizontal'];

    public static function key(): string
    {
        $key = strtolower(trim((string) config('starter.theme', 'tabler')));

        if (preg_match('/^[a-z0-9][a-z0-9_-]*$/', $key) !== 1
            || ! array_key_exists($key, (array) config('starter.themes', []))) {
            throw new RuntimeException("Unsupported starter theme [{$key}].");
        }

        return $key;
    }

    public static function viewPath(string $path = ''): string
    {
        return self::configuredPath('views', $path);
    }

    public static function layout(): string
    {
        $layout = strtolower(trim((string) config('starter.layout', 'vertical')));

        if (! self::supportsLayout($layout)) {
            throw new RuntimeException("Unsupported starter layout [{$layout}].");
        }

        return $layout;
    }

    public static function supportsLayout(string $layout): bool
    {
        return in_array(strtolower(trim($layout)), self::LAYOUTS, true);
    }

    public static function assetPath(string $path = ''): string
    {
        return self::configuredPath('assets', $path);
    }

    public static function docsPath(string $path = ''): string
    {
        return self::configuredPath('docs', $path);
    }

    /** @return class-string */
    public static function powerGridTheme(): string
    {
        $theme = config('starter.themes.'.self::key().'.powergrid');

        if (! is_string($theme) || $theme === '') {
            throw new RuntimeException('The active starter theme does not define a PowerGrid theme.');
        }

        return $theme;
    }

    private static function configuredPath(string $key, string $path): string
    {
        $relative = config('starter.themes.'.self::key().'.'.$key);

        if (! is_string($relative) || $relative === '' || str_contains($relative, '..')) {
            throw new RuntimeException("The active starter theme does not define a safe [{$key}] path.");
        }

        return StarterPaths::path($relative.($path === '' ? '' : '/'.ltrim($path, '/\\')));
    }
}
