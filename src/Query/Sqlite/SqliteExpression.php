<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Sqlite;

use Windwalker\Query\QueryExpression;

/**
 * Class SqliteExpression
 *
 * @since 2.0
 */
class SqliteExpression extends QueryExpression
{
    /**
     * Concatenates an array of column names or values.
     *
     * Usage:
     * $query->select($query->concatenate(array('a', 'b')));
     *
     * @param   array  $values    An array of values to concatenate.
     * @param   string $separator As separator to place between each value.
     *
     * @return  string  The concatenated values.
     *
     * @since   2.0
     */
    public function concatenate($values, $separator = null)
    {
        if ($separator) {
            return implode(' || ' . $this->query->quote($separator) . ' || ', $values);
        } else {
            return implode(' || ', $values);
        }
    }

    /**
     * Gets the number of characters in a string.
     *
     * Note, use 'length' to find the number of bytes in a string.
     *
     * Usage:
     * $query->select($query->charLength('a'));
     *
     * @param   string $field     A value.
     * @param   string $operator  Comparison operator between charLength integer value and $condition
     * @param   string $condition Integer value to compare charLength with.
     *
     * @return  string  The required char length call.
     *
     * @since   2.0
     *
     * @codingStandardsIgnoreStart
     */
    public function char_length($field, $operator = null, $condition = null)
    {
        // @codingStandardsIgnoreEnd
        return 'length(' . $field . ')' . ($operator !== null && $condition !== null
                ? ' ' . $operator . ' ' . $condition
                : '');
    }
}
