<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router\Compiler;

/**
 * The BasicGenerator class.
 *
 * @since  2.0
 */
abstract class BasicGenerator
{
    /**
     * generate
     *
     * @param string $pattern
     * @param array  $data
     *
     * @return  mixed|string
     */
    public static function generate($pattern, array $data = [])
    {
        $route = static::replaceOptionalSegments($pattern, $data);
        $route = static::replaceOptionalWildcards($route, $data);
        $route = static::replaceWildCards($route, $data);

        return static::replaceAllSegments($route, $data);
    }

    /**
     * replaceOptionalSegments
     *
     * @param string $route
     * @param array  &$data
     *
     * @return  mixed
     */
    protected static function replaceOptionalSegments($route, &$data)
    {
        preg_match(chr(1) . '\(/([a-z][a-zA-Z0-9_,]*)\)' . chr(1), $route, $matches);

        if (!$matches) {
            return $route;
        }

        $segments = explode(',', $matches[1]);

        foreach ($segments as $k => $segment) {
            if (empty($data[$segment])) {
                unset($segments[$k]);
            }
        }

        $segments = $segments ? '/(' . implode(')/(', $segments) . ')' : '';

        $route = str_replace($matches[0], $segments, $route);

        return $route;
    }

    /**
     * replaceSegments
     *
     * @param string $route
     * @param array  &$data
     *
     * @return  mixed|string
     */
    protected static function replaceAllSegments($route, &$data)
    {
        preg_match_all(chr(1) . '\(([a-z][a-zA-Z0-9_]*)\)' . chr(1), $route, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (isset($data[$match[1]])) {
                $route = str_replace($match[0], $data[$match[1]], $route);

                unset($data[$match[1]]);
            }
        }

        $queries = http_build_query($data);

        if ($queries) {
            $route = rtrim($route, '/') . '?' . $queries;
        }

        return $route;
    }

    /**
     * replaceOptionalWildcards
     *
     * @param string $route
     * @param array  &$data
     *
     * @return  string|string[]
     *
     * @since  3.5.22
     */
    protected static function replaceOptionalWildcards($route, &$data)
    {
        preg_match_all(chr(1) . '\(/\*([a-z][a-zA-Z0-9_]*)\)' . chr(1), $route, $matches, PREG_SET_ORDER);

        if (!$matches) {
            return $route;
        }

        foreach ($matches as $match) {
            if (isset($data[$match[1]])) {
                $route = str_replace($match[0], '/' . implode('/', (array) $data[$match[1]]), $route);

                unset($data[$match[1]]);
            }
        }

        return $route;
    }

    /**
     * replaceWildCards
     *
     * @param string $route
     * @param array  &$data
     *
     * @return  mixed
     */
    protected static function replaceWildCards($route, &$data)
    {
        preg_match_all(chr(1) . '\(\*([a-z][a-zA-Z0-9_]*)\)' . chr(1), $route, $matches, PREG_SET_ORDER);

        if (!$matches) {
            return $route;
        }

        foreach ($matches as $match) {
            if (isset($data[$match[1]])) {
                $route = str_replace($match[0], implode('/', (array) $data[$match[1]]), $route);

                unset($data[$match[1]]);
            }
        }

        return $route;
    }
}
