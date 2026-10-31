<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use ArrayIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveArrayIterator;
use Windwalker\Utilities\Iterator\DualIterator;

class DualIteratorTest extends TestCase
{
    /**
     * @see  DualIterator::current
     * @see  DualIterator::key
     */
    #[Test]
    #[DataProvider('flagsProvider')]
    public function currentAndKeyFollowFlags(int $flags, mixed $expectedCurrent, mixed $expectedKey): void
    {
        $it = new DualIterator(
            new ArrayIterator(['a' => 1]),
            new ArrayIterator(['b' => 2]),
            $flags
        );

        $it->rewind();

        self::assertSame($expectedCurrent, $it->current());
        self::assertSame($expectedKey, $it->key());
    }

    public static function flagsProvider(): array
    {
        return [
            'default flags' => [DualIterator::DEFAULT_FLAGS, [1, 2], ['a', 'b']],
            'lhs' => [DualIterator::CURRENT_LHS | DualIterator::KEY_LHS, 1, 'a'],
            'rhs' => [DualIterator::CURRENT_RHS | DualIterator::KEY_RHS, 2, 'b'],
            'none' => [DualIterator::CURRENT_0 | DualIterator::KEY_0, null, null],
        ];
    }

    /**
     * @see  DualIterator::valid
     * @see  DualIterator::next
     */
    #[Test]
    public function iterationStopsWhenEitherSideEnds(): void
    {
        $it = new DualIterator(new ArrayIterator([1, 2, 3]), new ArrayIterator(['a', 'b']));

        self::assertSame([[1, 'a'], [2, 'b']], iterator_to_array($it, false));
    }

    /**
     * @see  DualIterator::areIdentical
     * @see  DualIterator::areEqual
     */
    #[Test]
    public function areEqualIgnoresTypeButAreIdenticalDoesNot(): void
    {
        $it = new DualIterator(new ArrayIterator([1]), new ArrayIterator(['1']));
        $it->rewind();

        self::assertTrue($it->areEqual());
        self::assertFalse($it->areIdentical());
    }

    /**
     * @see  DualIterator::areIdentical
     * @see  DualIterator::areEqual
     */
    #[Test]
    public function areEqualAndIdenticalAreTrueWhenBothSidesAreExhausted(): void
    {
        $it = new DualIterator(new ArrayIterator([]), new ArrayIterator([]));
        $it->rewind();

        self::assertTrue($it->areEqual());
        self::assertTrue($it->areIdentical());

        $it = new DualIterator(new ArrayIterator([1]), new ArrayIterator([]));
        $it->rewind();

        self::assertFalse($it->areEqual());
        self::assertFalse($it->areIdentical());
    }

    /**
     * @see  DualIterator::compareIterators
     */
    #[Test]
    #[DataProvider('compareProvider')]
    public function compareIteratorsComparesFlatIterators(
        array $left,
        array $right,
        bool $identical,
        bool $expected
    ): void {
        self::assertSame(
            $expected,
            DualIterator::compareIterators(new ArrayIterator($left), new ArrayIterator($right), $identical)
        );
    }

    public static function compareProvider(): array
    {
        return [
            'same loosely' => [[1, 2], ['1', '2'], false, true],
            'same strictly' => [[1, 2], [1, 2], true, true],
            'strict type differs' => [[1, 2], ['1', '2'], true, false],
            'value differs' => [[1, 2], [1, 3], false, false],
            'different length' => [[1, 2], [1, 2, 3], false, false],
            'different length strict' => [[1, 2, 3], [1, 2], true, false],
            'both empty' => [[], [], true, true],
        ];
    }

    /**
     * @see  DualIterator::compareIterators
     */
    #[Test]
    public function compareIteratorsReturnsFalseWhenOnlyOneSideIsRecursive(): void
    {
        self::assertFalse(
            DualIterator::compareIterators(
                new RecursiveArrayIterator([1, [2]]),
                new ArrayIterator([1, [2]])
            )
        );
    }

    /**
     * @see  DualIterator::compareIterators
     */
    #[Test]
    #[DataProvider('recursiveCompareProvider')]
    public function compareIteratorsComparesRecursiveIterators(
        array $left,
        array $right,
        bool $identical,
        bool $expected
    ): void {
        self::assertSame(
            $expected,
            DualIterator::compareIterators(
                new RecursiveArrayIterator($left),
                new RecursiveArrayIterator($right),
                $identical
            )
        );
    }

    public static function recursiveCompareProvider(): array
    {
        return [
            'same nested' => [[1, [2, 3]], [1, [2, 3]], false, true],
            'same nested strictly' => [[1, [2, 3]], [1, [2, 3]], true, true],
            'nested value differs' => [[1, [2, 3]], [1, [2, 4]], false, false],
            'nested shorter' => [[1, [2, 3]], [1, [2]], false, false],
        ];
    }
}
