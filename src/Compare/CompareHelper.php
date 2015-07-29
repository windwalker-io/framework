<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Compare;

/**
 * The CompareHelper class.
 * 
 * @since  2.0
 */
class CompareHelper
{
	/**
	 * Compare two values.
	 *
	 * @param mixed  $compare1 The compare1 value.
	 * @param string $operator The compare operator.
	 * @param mixed  $compare2 The compare2 calue.
	 * @param bool   $strict   Use strict compare.
	 *
	 * @return  boolean
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function compare($compare1, $operator, $compare2, $strict = false)
	{
		$operator = trim(strtolower($operator));

		switch ($operator)
		{
			case '=':
			case '==':
			case 'eq':
				return $strict ? $compare1 === $compare2 : $compare1 == $compare2;
				break;

			case '===':
				return $compare1 === $compare2;
				break;

			case '!=':
			case 'neq':
				return $strict ? $compare1 !== $compare2 : $compare1 != $compare2;
				break;

			case '!==':
				return $compare1 !== $compare2;
				break;

			case '>':
			case 'gt':
				return $compare1 > $compare2;
				break;

			case '>=':
			case 'gte':
				return $compare1 >= $compare2;
				break;

			case '<':
			case 'lt':
				return $compare1 < $compare2;
				break;

			case '<=':
			case 'lte':
				return $compare1 <= $compare2;
				break;

			case 'in':
				return in_array($compare1, static::toArray($compare2), $strict);
				break;

			case 'not in':
			case 'not-in':
			case 'notin':
			case 'nin':
				return !in_array($compare1, static::toArray($compare2), $strict);
				break;

			default:
				throw new \InvalidArgumentException('Invalid compare operator: ' . $operator);
		}
	}

	/**
	 * Method to convert object and iterator to array.
	 *
	 * @param mixed $array THe value to convert.
	 *
	 * @return  array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function toArray($array)
	{
		if ($array instanceof \Traversable)
		{
			$array = iterator_to_array($array);
		}
		elseif (is_object($array))
		{
			$array = get_object_vars($array);
		}

		if (!is_array($array))
		{
			throw new \InvalidArgumentException('In compare must have compare2 as array.');
		}

		return $array;
	}
}
