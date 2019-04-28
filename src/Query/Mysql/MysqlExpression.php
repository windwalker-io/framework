<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\QueryExpression;

/**
 * Class MysqlExpression
 *
 * @since 2.0
 */
class MysqlExpression extends QueryExpression
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
            $concat_string = 'CONCAT_WS(' . $this->query->quote($separator);

            foreach ($values as $value) {
                $concat_string .= ', ' . $value;
            }

            return $concat_string . ')';
        } else {
            return 'CONCAT(' . implode(',', $values) . ')';
        }
    }
}
