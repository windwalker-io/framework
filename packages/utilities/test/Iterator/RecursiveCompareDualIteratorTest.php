<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveArrayIterator;
use Windwalker\Utilities\Iterator\DualIterator;
use Windwalker\Utilities\Iterator\RecursiveCompareDualIterator;
use Windwalker\Utilities\Iterator\RecursiveDualIterator;

class RecursiveCompareDualIteratorTest extends TestCase
{
    /**
     * @see  RecursiveCompareDualIterator::areEqual
     * @see  RecursiveCompareDualIterator::areIdentical
     */
    #[Test]
    public function areEqualAndIdenticalCompareLeavesOfBothTrees(): void
    {
        $it = $this->createIterator([1, [2, 3]], ['1', [2, 3]]);

        foreach ($it as $_) {
            self::assertTrue($it->areEqual());
        }

        $it->rewind();

        self::assertTrue($it->areEqual());
        self::assertFalse($it->areIdentical());
    }

    /**
     * @see  RecursiveCompareDualIterator::endChildren
     */
    #[Test]
    public function endChildrenMarksIteratorUnequalWhenOneBranchIsLonger(): void
    {
        $it = $this->createIterator([[1, 2]], [[1]]);

        foreach ($it as $_) {
            //
        }

        self::assertFalse($it->areEqual());
    }

    /**
     * @see  RecursiveDualIterator::hasChildren
     * @see  RecursiveDualIterator::getChildren
     */
    #[Test]
    public function recursiveDualIteratorOnlyDescendsWhenBothSidesHaveChildren(): void
    {
        $it = new RecursiveDualIterator(
            new RecursiveArrayIterator([[1], [2]]),
            new RecursiveArrayIterator([[1], 2]),
            DualIterator::CURRENT_0 | DualIterator::KEY_0
        );

        $it->rewind();
        self::assertTrue($it->hasChildren());
        self::assertInstanceOf(RecursiveDualIterator::class, $it->getChildren());

        $it->next();
        self::assertFalse($it->hasChildren());
    }

    private function createIterator(array $left, array $right): RecursiveCompareDualIterator
    {
        return new RecursiveCompareDualIterator(
            new RecursiveDualIterator(
                new RecursiveArrayIterator($left),
                new RecursiveArrayIterator($right),
                DualIterator::CURRENT_0 | DualIterator::KEY_0
            )
        );
    }
}
