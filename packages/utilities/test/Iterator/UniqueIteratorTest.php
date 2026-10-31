<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use ArrayIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\TestAccessorTrait;
use Windwalker\Utilities\Iterator\UniqueIterator;

/**
 * The UniqueIteratorTest class.
 */
class UniqueIteratorTest extends TestCase
{
    use TestAccessorTrait;

    /**
     * @see  UniqueIterator::accept
     */
    #[Test]
    public function acceptYieldsOnlyFirstOccurrenceOfEachValue(): void
    {
        $iter = new UniqueIterator(new ArrayIterator(['a', 'b', 'a', 'c', 'd', 'd', 'b']));

        self::assertEquals(['a', 'b', 'c', 'd'], array_values(iterator_to_array($iter)));
    }

    /**
     * @see  UniqueIterator::rewind
     */
    #[Test]
    public function rewindResetsSeenValuesToFirstItem(): void
    {
        $iter = new UniqueIterator(new ArrayIterator(['a', 'b', 'a', 'c', 'd', 'd', 'b']));

        foreach ($iter as $item) {
            //
        }

        $iter->rewind();

        self::assertEquals(['a'], $this->getValue($iter, 'exists'));
    }
}
