<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\String\Compare;

use Windwalker\String\String;

/**
 * Class AbstractCompare
 *
 * @since 1.0
 */
class StringCompare
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
	 * Property compare1Quote.
	 *
	 * @var  string
	 */
	protected $compare1Quote = null;

	/**
	 * Property compare2Quote.
	 *
	 * @var  string
	 */
	protected $compare2Quote = null;

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
		$return = '';

		if ($this->compare1)
		{
			$return .= ($this->compare1Quote ? String::quote($this->compare1, $this->compare1Quote) : $this->compare1) . ' ';
		}

		$return .= $this->operator;

		if ($this->compare2)
		{
			$return .= ' ' . ($this->compare2Quote ? String::quote($this->compare2, $this->compare2Quote) : $this->compare2);
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
	 * @return  StringCompare  Return self to support chaining.
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
	 * @return  StringCompare  Return self to support chaining.
	 */
	public function setCompare1($compare1)
	{
		$this->compare1 = $compare1;

		return $this;
	}

	/**
	 * setCompare1Quote
	 *
	 * @param   string $compare1Quote
	 *
	 * @return  StringCompare  Return self to support chaining.
	 */
	public function setCompare1Quote($compare1Quote)
	{
		$this->compare1Quote = $compare1Quote;

		return $this;
	}

	/**
	 * setCompare2Quote
	 *
	 * @param   string $compare2Quote
	 *
	 * @return  StringCompare  Return self to support chaining.
	 */
	public function setCompare2Quote($compare2Quote)
	{
		$this->compare2Quote = $compare2Quote;

		return $this;
	}

	/**
	 * setQuote
	 *
	 * @param string $quote
	 *
	 * @return  StringCompare  Return self to support chaining.
	 */
	public function setQuote($quote)
	{
		$this->setCompare1Quote($quote)
			->setCompare2Quote($quote);

		return $this;
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
}
