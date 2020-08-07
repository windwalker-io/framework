<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query;

/**
 * Class QueryGrammar
 *
 * @since 2.0
 */
abstract class AbstractQueryGrammar implements QueryGrammarInterface
{
    /**
     * Property query.
     *
     * @var  Query
     */
    public static $query = null;

    /**
     * Property instance.
     *
     * @var  QueryGrammarInterface[]
     */
    protected static $instance = [];

    /**
     * getInstance
     *
     * @param   string $name
     *
     * @return  QueryGrammarInterface
     */
    public static function getInstance($name)
    {
        if (!isset(static::$instance[strtolower($name)])) {
            $name = ucfirst($name);

            static::$instance[strtolower($name)] = sprintf(__NAMESPACE__ . '\%s\%sGrammar', $name, $name);
        }

        return static::$instance[strtolower($name)];
    }

    /**
     * build
     *
     * @return  string
     */
    public static function build()
    {
        $args = func_get_args();

        $sql = [];

        foreach ($args as $arg) {
            if ($arg === '' || $arg === null || $arg === false) {
                continue;
            }

            $sql[] = $arg;
        }

        return implode(' ', $args);
    }

    /**
     * getQuery
     *
     * @param bool $new
     *
     * @return  Query
     */
    public static function getQuery($new = false)
    {
        if (!static::$query || $new) {
            static::$query = new Query();
        }

        return static::$query;
    }

    /**
     * buildJsonSelector
     *
     * @param  string  $column
     * @param  array   $paths
     * @param  bool    $unQuoteLast
     *
     * @return  string
     *
     * @since  3.5.21
     */
    public static function buildJsonSelector(string $column, array $paths, bool $unQuoteLast = true): string
    {
        throw new \LogicException('This DB does not support JSON.');
    }
}
