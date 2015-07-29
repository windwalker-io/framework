<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Compare;

/**
 * Class InCompare
 *
 * @since 2.0
 */
class InCompare extends Compare
{
	/**
	 * Operator symbol.
	 *
	 * @var  string
	 */
	protected $operator = 'IN';

	/**
	 * The separator symbol.
	 *
	 * @var  string
	 */
	protected $separator = ',';

	/**
	 * Do compare.
	 *
	 * @param bool $strict Use strict compare.
	 *
	 * @return  boolean  The result of compare.
	 */
	public function compare($strict = false)
	{
		$compare2 = $this->compare2;

		if (is_string($this->compare2))
		{
			$compare2 = explode($this->separator, $this->compare2);

			$compare2 = array_map('trim', $compare2);
		}

		return CompareHelper::compare($this->compare1, $this->operator, $compare2, $strict);
	}

	/**
	 * Convert to string.
	 *
	 * @param string $quote1 Quote compare1.
	 * @param string $quote2 Quote compare2.
	 *
	 * @return  string
	 */
	public function toString($quote1 = null, $quote2 = null)
	{
		if (is_callable($this->handler))
		{
			return call_user_func_array($this->handler, array($this->compare1, $this->compare2, $this->operator, $quote1, $quote2));
		}

		$return = array();

		if ($this->compare1)
		{
			$return[] = $quote1 ? $this->quote($this->compare1, $quote1) : $this->compare1;
		}

		$return[] = $this->operator;

		if ($this->compare2)
		{
			$compare2 = $this->compare2;

			if (is_string($compare2))
			{
				$compare2 = explode($this->separator, $this->compare2);
			}

			$self = $this;

			$compare2 = array_map(
				function ($value) use ($quote2, $self)
				{
					return $self->quote($value, $quote2);
				},
				$compare2
			);

			$return[] = '(' . implode($this->separator, $compare2) . ')';
		}

		return implode(' ', $return);
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
