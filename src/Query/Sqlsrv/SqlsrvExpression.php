<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Sqlsrv;

use Windwalker\Query\QueryExpression;

/**
 * Class SqlservExpression
 *
 * @since 2.0
 */
class SqlsrvExpression extends QueryExpression
{
    /**
     * Gets the function to determine the length of a character string.
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
        return 'DATALENGTH(' . $field . ')' . ($operator !== null && $condition !== null
                ? ' ' . $operator . ' ' . $condition
                : '');
    }

    /**
     * Concatenates an array of column names or values.
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
            return '(' . implode('+' . $this->query->quote($separator) . '+', $values) . ')';
        }

        return '(' . implode('+', $values) . ')';
    }

    /**
     * Gets the current date and time.
     *
     * phpcs:disable
     *
     * @return  string
     *
     * @since   2.0
     */
    public function current_timestamp()
    {
        // phpcs:enable
        return 'GETDATE()';
    }

    /**
     * Get the length of a string in bytes.
     *
     * @param   string $value The string to measure.
     *
     * @return  integer
     *
     * @since   2.0
     */
    public function length($value)
    {
        return 'LEN(' . $value . ')';
    }

    /**
     * Casts a value to a char.
     *
     * Ensure that the value is properly quoted before passing to the method.
     *
     * @param   string $value The value to cast as a char.
     *
     * @return  string  Returns the cast value.
     *
     * @since   2.0
     *
     * @codingStandardsIgnoreStart
     */
    public function cast_as_char($value)
    {
        // @codingStandardsIgnoreEnd
        return 'CAST(' . $value . ' as NVARCHAR(10))';
    }
}
