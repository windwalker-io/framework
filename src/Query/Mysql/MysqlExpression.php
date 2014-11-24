<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\QueryExpression;

/**
 * Class MysqlExpression
 *
 * @since {DEPLOY_VERSION}
 */
class MysqlExpression extends QueryExpression
{
	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			$concat_string = 'CONCAT_WS(' . $this->query->quote($separator);

			foreach ($values as $value)
			{
				$concat_string .= ', ' . $value;
			}

			return $concat_string . ')';
		}
		else
		{
			return 'CONCAT(' . implode(',', $values) . ')';
		}
	}
}

