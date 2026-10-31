<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use RecursiveIterator;

/**
 * The RecursiveDualIterator class.
 *
 * @since  2.0
 */
class RecursiveDualIterator extends DualIterator implements RecursiveIterator
{
    /**
     * Construct from two RecursiveIterators.
     *
     * @param  RecursiveIterator  $lhs    Left Hand Side Iterator
     * @param  RecursiveIterator  $rhs    Right Hand Side Iterator
     * @param  int                $flags  Iteration flags
     */
    public function __construct(RecursiveIterator $lhs, RecursiveIterator $rhs, int $flags = self::DEFAULT_FLAGS)
    {
        parent::__construct($lhs, $rhs, $flags);
    }

    /**
     * Whether both inner iterators have children at the current position.
     */
    public function hasChildren(): bool
    {
        return $this->getLHS()->hasChildren() && $this->getRHS()->hasChildren();
    }

    /**
     * Create a dual iterator from the children of both inner iterators.
     */
    public function getChildren(): RecursiveDualIterator
    {
        return new static(
            $this->getLHS()->getChildren(),
            $this->getRHS()->getChildren(),
            $this->getFlags()
        );
    }
}
