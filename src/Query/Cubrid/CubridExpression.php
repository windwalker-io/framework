<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Cubrid;

use Windwalker\Query\QueryExpression;

/**
 * Class CubridExpression
 *
 * @since 2.0
 */
class CubridExpression extends QueryExpression
{
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
            return implode(' || ' . $this->query->quote($separator) . ' || ', $values);
        } else {
            return 'CONCAT(' . implode(',', $values) . ')';
        }
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
     */
    public function cast_as_char($value)
    {
        return "CAST(" . $value . " AS CHAR)";
    }
}
