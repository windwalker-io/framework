<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare;

/**
 * The compare object.
 *
 * @since 2.0
 */
class Compare
{
	/**
	 * Operator symbol.
	 *
	 * @var  string
	 */
	protected $operator = '';

	/**
	 * Compare 1, at left.
	 *
	 * @var  string
	 */
	protected $compare1;

	/**
	 * Compare 2, at right.
	 *
	 * @var  string
	 */
	protected $compare2;

	/**
	 * The compare callback.
	 *
	 * @var  callable
	 */
	protected $handler = null;

	/**
	 * Constructor.
	 *
	 * @param string $compare1 Compare 1, at left.
	 * @param string $compare2 Compare 2, at right.
	 * @param null   $operator The operator symbol.
	 */
	public function __construct($compare1 = null, $compare2 = null, $operator = null)
	{
		$this->compare1 = $compare1;
		$this->compare2 = $compare2;

		$this->operator = $operator ? : $this->operator;
	}

	/**
	 * Convert to string.
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
			$return .= ' ' . $this->compare2;
		}

		return $return;
	}

	/**
	 * Magic method to convert this to string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			echo '<pre>' . $e . '</pre>';
			exit;
		}
	}

	/**
	 * Compare 2 getter.
	 *
	 * @return  string
	 */
	public function getCompare2()
	{
		return $this->compare2;
	}

	/**
	 * Compare 2 setter.
	 *
	 * @param   string $compare2 Compare 2.
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setCompare2($compare2)
	{
		$this->compare2 = $compare2;

		return $this;
	}

	/**
	 * Compare 1 getter.
	 *
	 * @return  string
	 */
	public function getCompare1()
	{
		return $this->compare1;
	}

	/**
	 * Compare 1 setter.
	 *
	 * @param   string $compare1 Compare 1.
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setCompare1($compare1)
	{
		$this->compare1 = $compare1;

		return $this;
	}

	/**
	 * Flip compares.
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function flipCompare()
	{
		$compare1 = $this->compare1;

		$this->compare1 = $this->compare2;

		$this->compare2 = $compare1;

		return $this;
	}

	/**
	 * Do compare.
	 *
	 * @return  boolean  The result of compare.
	 */
	public function compare()
	{
		$result = false;

		eval('$result = $this->compare1 ' . $this->operator . ' $this->compare2');

		return $result;
	}

	/**
	 * Operator getter.
	 *
	 * @return  string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * Quote our compare string.
	 *
	 * @param   string $string The string to quote.
	 * @param   string $quote  The quote symbol.
	 *
	 * @return  string Quoted string.
	 */
	public function quote($string, $quote = "''")
	{
		if (empty($quote[1]))
		{
			$quote[1] = $quote[0];
		}

		return $quote[0] . $string . $quote[1];
	}

	/**
	 * Get handler.
	 *
	 * @return  callable
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Ser handler.
	 *
	 * @param   callable $handler The compare handler.
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}
}
