<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare;

/**
 * Class AbstractCompare
 *
 * @since 1.0
 */
class Compare
{
	/**
	 * Property operator.
	 *
	 * @var  string
	 */
	protected $operator = '';

	/**
	 * Property compare1.
	 *
	 * @var  string
	 */
	protected $compare1;

	/**
	 * Property compare2.
	 *
	 * @var  string
	 */
	protected $compare2;

	/**
	 * Property handler.
	 *
	 * @var  callable
	 */
	protected $handler = null;

	/**
	 * Constractor.
	 *
	 * @param string $compare1
	 * @param string $compare2
	 * @param null   $operator
	 */
	public function __construct($compare1 = null, $compare2 = null, $operator = null)
	{
		$this->compare1 = $compare1;
		$this->compare2 = $compare2;

		$this->operator = $operator ? : $this->operator;
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
			$return .= ' ' . $this->compare2;
		}

		return $return;
	}

	/**
	 * __toString
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

		return '';
	}

	/**
	 * getCompare2
	 *
	 * @return  string
	 */
	public function getCompare2()
	{
		return $this->compare2;
	}

	/**
	 * setCompare2
	 *
	 * @param   string $compare2
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setCompare2($compare2)
	{
		$this->compare2 = $compare2;

		return $this;
	}

	/**
	 * getCompare1
	 *
	 * @return  string
	 */
	public function getCompare1()
	{
		return $this->compare1;
	}

	/**
	 * setCompare1
	 *
	 * @param   string $compare1
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setCompare1($compare1)
	{
		$this->compare1 = $compare1;

		return $this;
	}

	/**
	 * flipCompare
	 *
	 * @return  $this
	 */
	public function flipCompare()
	{
		$compare1 = $this->compare1;

		$this->compare1 = $this->compare2;

		$this->compare2 = $compare1;

		return $this;
	}

	/**
	 * compare
	 *
	 * @return  boolean
	 */
	public function compare()
	{
		return eval('$this->compare1 ' . $this->operator . ' $this->compare2');
	}

	/**
	 * getOperator
	 *
	 * @return  string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * quote
	 *
	 * @param string $string
	 * @param string $quote
	 *
	 * @return  string
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
	 * getHandler
	 *
	 * @return  callable
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * setHandler
	 *
	 * @param   callable $handler
	 *
	 * @return  Compare  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}
}
