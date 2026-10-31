<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Iterator\PaginateIterator;

class PaginateIteratorTest extends TestCase
{
    /**
     * @see  PaginateIterator::getIterator
     */
    #[Test]
    public function getIteratorFetchesPagesUntilAnEmptyPage(): void
    {
        $pages = [1 => ['a', 'b'], 2 => ['c', 'd'], 3 => ['e'], 4 => []];
        $calls = [];

        $iter = new PaginateIterator(
            static function (int $page, int $perPage) use ($pages, &$calls) {
                $calls[] = [$page, $perPage];

                return $pages[$page] ?? [];
            },
            2
        );

        self::assertSame(['a', 'b', 'c', 'd', 'e'], iterator_to_array($iter, false));
        self::assertSame([[1, 2], [2, 2], [3, 2], [4, 2]], $calls);
    }

    /**
     * @see  PaginateIterator::getIterator
     */
    #[Test]
    public function getIteratorStopsOnceLimitIsReached(): void
    {
        $calls = 0;

        $iter = new PaginateIterator(
            static function (int $page, int $perPage) use (&$calls) {
                $calls++;

                return range(($page - 1) * $perPage + 1, $page * $perPage);
            },
            3,
            5
        );

        self::assertSame([1, 2, 3, 4, 5, 6], iterator_to_array($iter, false));
        self::assertSame(2, $calls);
    }
}
