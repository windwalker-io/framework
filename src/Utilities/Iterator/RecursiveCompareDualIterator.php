<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Iterator;

/**
 * The RecursiveCompareDualIterator class.
 * 
 * @since  2.0
 */
class RecursiveCompareDualIterator extends \RecursiveIteratorIterator
{
	/**
	 * Used to keep end of recursion equality. That is en leaving a nesting
	 * level we need to check whether both child iterators are at their end.
	 *
	 * @var boolean
	 */
	protected $equal = false;

	/**
	 * Construct from RecursiveDualIterator
	 *
	 * @param RecursiveDualIterator $it     RecursiveDualIterator
	 * @param integer               $mode   Should be LEAVES_ONLY
	 * @param integer               $flags  Should be 0
	 */
	public function __construct(RecursiveDualIterator $it, $mode = self::LEAVES_ONLY, $flags = 0)
	{
		parent::__construct($it);
	}

	/**
	 * Rewind iteration and comparison process. Starting with $equal = true.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->equal = true;

		parent::rewind();
	}

	/**
	 * Calculate $equal
	 *
	 * @see $equal
	 *
	 * @return void
	 */
	public function endChildren()
	{
		$this->equal &= !$this->getInnerIterator()->getLHS()->valid()
			&& !$this->getInnerIterator()->getRHS()->valid();
	}

	/**
	 * Are Identical.
	 *
	 * @return boolean Whether both inner iterators are valid and have identical
	 *                 current and key values or both are non valid.
	 */
	public function areIdentical()
	{
		return $this->equal && $this->getInnerIterator()->areIdentical();
	}

	/**
	 * Are equal.
	 *
	 * @return boolean Whether both inner iterators are valid and have equal current
	 *                 and key values or both are non valid.
	 */
	public function areEqual()
	{
		return $this->equal && $this->getInnerIterator()->areEqual();
	}
}
