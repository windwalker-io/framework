<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare;

/**
 * Class InCompare
 *
 * @since 1.0
 */
class InCompare extends Compare
{
	/**
	 * Property operator.
	 *
	 * @var  string
	 */
	protected $operator = 'IN';

	/**
	 * Property separator.
	 *
	 * @var  string
	 */
	protected $separator = ',';

	/**
	 * compare
	 *
	 * @return  mixed
	 */
	public function compare()
	{
		$compare2 = is_string($this->compare2) ? explode($this->separator, $this->compare2) : (array) $this->compare2;

		$compare2 = array_map('trim', $compare2);

		return in_array($this->compare1, $compare2);
	}

	/**
	 * toString
	 *
	 * @return  string
	 */
	public function toString()
	{
		if (is_callable($this->handler))
		{
			return call_user_func_array($this->handler, array($this->compare1, $this->compare2, $this->operator));
		}

		$return = '';

		if ($this->compare1)
		{
			$return .= $this->compare1 . ' ';
		}

		$return .= $this->operator;

		if ($this->compare2)
		{
			$return .= ' (' . implode(',', $this->compare2) . ')';
		}

		return $return;
	}

	/**
	 * getSeparator
	 *
	 * @return  string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * setSeparator
	 *
	 * @param   string $separator
	 *
	 * @return  InCompare  Return self to support chaining.
	 */
	public function setSeparator($separator)
	{
		$this->separator = $separator;

		return $this;
	}
}
