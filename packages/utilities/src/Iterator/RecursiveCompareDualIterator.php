<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use RecursiveIteratorIterator;

/**
 * The RecursiveCompareDualIterator class.
 *
 * @since  2.0
 */
class RecursiveCompareDualIterator extends RecursiveIteratorIterator
{
    /**
     * Used to keep end of recursion equality. That is en leaving a nesting
     * level we need to check whether both child iterators are at their end.
     *
     * @var bool
     */
    protected bool $equal = false;

    /**
     * Construct from RecursiveDualIterator
     *
     * @param  RecursiveDualIterator  $it     RecursiveDualIterator
     * @param  integer                $mode   Should be LEAVES_ONLY
     * @param  integer                $flags  Should be 0
     */
    public function __construct(RecursiveDualIterator $it, int $mode = self::LEAVES_ONLY, int $flags = 0)
    {
        parent::__construct($it);
    }

    /**
     * Rewind iteration and comparison process. Starting with $equal = true.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->equal = true;

        parent::rewind();
    }

    /**
     * Calculate $equal
     *
     * @return void
     * @see $equal
     *
     */
    public function endChildren(): void
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
    public function areIdentical(): bool
    {
        return $this->equal && $this->getInnerIterator()->areIdentical();
    }

    /**
     * Are equal.
     *
     * @return boolean Whether both inner iterators are valid and have equal current
     *                 and key values or both are non valid.
     */
    public function areEqual(): bool
    {
        return $this->equal && $this->getInnerIterator()->areEqual();
    }
}
