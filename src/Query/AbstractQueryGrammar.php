<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
}
