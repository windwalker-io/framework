<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router\Compiler;

use Windwalker\Router\RouteHelper;

/**
 * The TrieGenerator class.
 *
 * @since  2.0
 */
abstract class TrieGenerator
{
    /**
     * Property vars.
     *
     * @var array
     */
    protected static $vars = [];

    /**
     * generate
     *
     * @param string $pattern
     * @param array  $queries
     *
     * @return  mixed|string
     */
    public static function generate($pattern, array $queries = [])
    {
        $replace = [];

        $pattern = RouteHelper::sanitize($pattern);

        if (!isset(static::$vars[$pattern])) {
            TrieCompiler::compile($pattern);

            static::$vars[$pattern] = (array) TrieCompiler::$vars;
        }

        foreach (static::$vars[$pattern] as $key) {
            $var = isset($queries[$key]) ? $queries[$key] : 'null';

            if (is_array($var) || is_object($var)) {
                $var = implode('/', (array) $var);

                $key2 = '*' . $key;

                $replace[$key2] = $var;
            } else {
                $key2 = ':' . $key;

                $replace[$key2] = $var;
            }

            if (strpos($pattern, $key2) !== false) {
                unset($queries[$key]);
            }
        }

        $pattern = strtr($pattern, $replace);

        $queries = http_build_query($queries);

        if ($queries) {
            $pattern = rtrim($pattern, '/') . '/?' . $queries;
        }

        return $pattern;
    }
}
