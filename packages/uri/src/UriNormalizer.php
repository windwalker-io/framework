<?php

declare(strict_types=1);

namespace Windwalker\Uri;

use Windwalker\Utilities\Str;

/**
 * The UriNormalizer class.
 */
class UriNormalizer
{
    public static function clean(string $uri): string
    {
        [$scheme, $path] = array_pad(explode('://', $uri), -2, null);

        return $scheme . '://' . static::cleanPath($path);
    }

    public static function cleanPath(string $path): string
    {
        return preg_replace('#(/+)#', '/', $path);
    }

    /**
     * Resolves //, ../ and ./ from a path and returns
     * the result. Eg:
     *
     * /foo/bar/../boo.php    => /foo/boo.php
     * /foo/bar/../../boo.php => /boo.php
     * /foo/bar/.././/boo.php => /foo/boo.php
     *
     * @param  string  $path  The URI path to clean.
     *
     * @return  string  Cleaned and resolved URI path.
     *
     * @since   2.0
     */
    public static function normalizePath(string $path): string
    {
        $paths = explode('/', static::cleanPath($path));

        for ($i = 0, $n = count($paths); $i < $n; $i++) {
            if ($paths[$i] === '.' || $paths[$i] === '..') {
                if (($paths[$i] === '.') || ($paths[$i] === '..' && $i == 1 && $paths[0] === '')) {
                    unset($paths[$i]);
                    $paths = array_values($paths);
                    $i--;
                    $n--;
                } elseif ($paths[$i] === '..' && ($i > 1 || ($i == 1 && $paths[0] !== ''))) {
                    unset($paths[$i], $paths[$i - 1]);
                    $paths = array_values($paths);
                    $i -= 2;
                    $n -= 2;
                }
            }
        }

        return implode('/', $paths);
    }

    public static function normalize(string $uri): string
    {
        [$scheme, $path] = array_pad(explode('://', $uri), -2, null);

        return $scheme . '://' . static::normalizePath($path);
    }

    public static function ensureRoot(string $path, string $ds = '/'): string
    {
        return Str::ensureLeft($path, $ds);
    }

    public static function ensureDir(string $path, string $ds = '/'): string
    {
        return Str::ensureRight($path, $ds);
    }
}
