<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Iterator;

/**
 * The DualIterator class.
 *
 * @note    This is a fork from Marcus Boerger's work:
 *          https://github.com/php/php-src/blob/master/ext/spl/examples/dualiterator.inc
 * 
 * @since  2.0
 */
class DualIterator implements \Iterator
{
	const CURRENT_LHS   = 0x01;
	const CURRENT_RHS   = 0x02;
	const CURRENT_ARRAY = 0x03;
	const CURRENT_0     = 0x00;

	const KEY_LHS   = 0x10;
	const KEY_RHS   = 0x20;
	const KEY_ARRAY = 0x30;
	const KEY_0     = 0x00;

	const DEFAULT_FLAGS = 0x33;

	/**
	 * Property lhs.
	 *
	 * @var  \Iterator
	 */
	private $lhs;

	/**
	 * Property rhs.
	 *
	 * @var  \Iterator
	 */
	private $rhs;

	/**
	 * Property flags.
	 *
	 * @var  int
	 */
	private $flags;

	/** construct iterator from two iterators
	 *
	 * @param \Iterator $lhs    Left  Hand Side Iterator
	 * @param \Iterator $rhs    Right Hand Side Iterator
	 * @param int       $flags  Iteration flags
	 */
	public function __construct(\Iterator $lhs, \Iterator $rhs, $flags = 0x33)
	{
		$this->lhs   = $lhs;
		$this->rhs   = $rhs;
		$this->flags = $flags;
	}

	/**
	 * Get lhs.
	 *
	 * @return \Iterator Left Hand Side Iterator
	 */
	public function getLHS()
	{
		return $this->lhs;
	}

	/**
	 * Get rhs.
	 *
	 * @return \Iterator Right Hand Side Iterator
	 */
	public function getRHS()
	{
		return $this->rhs;
	}

	/**
	 * Set flags.
	 *
	 * @param int $flags new flags
	 *
	 * @return void
	 */
	public function setFlags($flags)
	{
		$this->flags = $flags;
	}

	/**
	 * Get flag.
	 *
	 * @return int Current flags
	 */
	public function getFlags()
	{
		return $this->flags;
	}

	/**
	 * Rewind both inner iterators
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->lhs->rewind();
		$this->rhs->rewind();
	}

	/**
	 * Is valid.
	 *
	 * @return boolean whether both inner iterators are valid
	 */
	public function valid()
	{
		return $this->lhs->valid() && $this->rhs->valid();
	}

	/**
	 * Get current item.
	 *
	 * @return mixed current value depending on CURRENT_* flags
	 */
	public function current()
	{
		switch ($this->flags & 0x0F)
		{
			default:
			case self::CURRENT_ARRAY:
				return array($this->lhs->current(), $this->rhs->current());
			case self::CURRENT_LHS:
				return $this->lhs->current();
			case self::CURRENT_RHS:
				return $this->rhs->current();
			case self::CURRENT_0:
				return null;
		}
	}

	/**
	 * Get current key.
	 *
	 * @return mixed current value depending on KEY_* flags
	 */
	public function key()
	{
		switch ($this->flags & 0xF0)
		{
			default:
			case self::CURRENT_ARRAY:
				return array($this->lhs->key(), $this->rhs->key());
			case self::CURRENT_LHS:
				return $this->lhs->key();
			case self::CURRENT_RHS:
				return $this->rhs->key();
			case self::CURRENT_0:
				return null;
		}
	}

	/**
	 * Move both inner iterators forward
	 *
	 * @return void
	 */
	public function next()
	{
		$this->lhs->next();
		$this->rhs->next();
	}

	/**
	 * Are Identical.
	 *
	 * @return boolean Whether both inner iterators are valid and have identical
	 *                 current and key values or both are non valid.
	 */
	public function areIdentical()
	{
		return $this->valid()
			? $this->lhs->current() === $this->rhs->current()
			&& $this->lhs->key() === $this->rhs->key()
			: $this->lhs->valid() == $this->rhs->valid();
	}

	/**
	 * Are equal.
	 *
	 * @return boolean whether both inner iterators are valid and have equal current
	 * and key values or both are non valid.
	 */
	public function areEqual()
	{
		return $this->valid()
			? $this->lhs->current() == $this->rhs->current()
			&& $this->lhs->key() == $this->rhs->key()
			: $this->lhs->valid() == $this->rhs->valid();
	}

	/**
	 * Compare two iterators.
	 *
	 * @param \Iterator $lhs       Left  Hand Side Iterator
	 * @param \Iterator $rhs       Right Hand Side Iterator
	 * @param boolean   $identical Whether to use areEqual() or areIdentical()
	 *
	 * @return boolean whether both iterators are equal/identical
	 *
	 * @note If one implements RecursiveIterator the other must do as well.
	 *       And if both do then a recursive comparison is being used.
	 */
	public static function compareIterators(\Iterator $lhs, \Iterator $rhs, $identical = false)
	{
		if ($lhs instanceof \RecursiveIterator)
		{
			if ($rhs instanceof \RecursiveIterator)
			{
				$it = new RecursiveDualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
				$it = new RecursiveCompareDualIterator($it);
			}
			else
			{
				return false;
			}
		}
		else
		{
			$it = new DualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
		}

		if ($identical)
		{
			foreach ($it as $n)
			{
				if (!$it->areIdentical())
				{
					return false;
				}
			}
		}
		else
		{
			foreach ($it as $n)
			{
				if (!$it->areEqual())
				{
					return false;
				}
			}
		}

		return $identical ? $it->areIdentical() : $it->areEqual();
	}
}
