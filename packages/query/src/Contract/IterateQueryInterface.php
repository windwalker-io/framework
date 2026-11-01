<?php

declare(strict_types=1);

namespace Windwalker\Query\Contract;

use Generator;
use Traversable;
use Windwalker\Data\Collection;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Utilities\Iterator\PaginateIterator;
use Windwalker\Query\Query;

/**
 * Iterating and pagination related query methods.
 *
 * @psalm-type QueryCallback = \Closure(Query): mixed
 * @psalm-type QueryCallable = callable(Query): mixed
 */
interface IterateQueryInterface
{
    /**
     * @template T of Collection
     *
     * Iterate and call DB every N times, every time get full items but yield item one by one, stop when no more items.
     * This method can ensure no long connection to DB. You only need one level foreach to get every item.
     *
     * @param  CursorPaginate|int    $paginate
     * @param  class-string<T>|null  $class
     * @param  array                 $args
     *
     * @return  Generator<T>
     */
    public function iterateBatched(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator;

    /**
     * @template T of Collection
     *
     * Iterate and return chunks of items, every chunk will one time return full items.
     * You need 2 level foreach to get every item.
     *
     * @param  CursorPaginate|int    $paginate
     * @param  class-string<T>|null  $class
     * @param  array                 $args
     *
     * @return  Generator<Collection<T>>
     */
    public function iterateChunks(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator;

    /**
     * @template T of Collection
     *
     * Iterate and call DB N times, every time get innter iterator and yield items one by one, stop when no more items.
     * This method will not offset or paginated, it will always start from 0.
     * You must change the item state in the loop to avoid infinite loop.
     *
     * @param  int                   $length
     * @param  class-string<T>|null  $class
     * @param  array                 $args
     *
     * @return  Generator<T>
     */
    public function iterateWhile(int $length, ?string $class = null, array $args = []): \Generator;

    /**
     * @template T of Collection
     *
     * Iterate and call DB N times, every time get full items and yield items one by one to prevent long connections,
     * stop when no more items.
     * This method will not offset or paginated, it will always start from 0.
     * You must change the item state in the loop to avoid infinite loop.
     *
     * @param  int                   $length
     * @param  class-string<T>|null  $class
     * @param  array                 $args
     *
     * @return  Generator<T>
     */
    public function iterateBatchWhile(int $length, ?string $class = null, array $args = []): \Generator;

    /**
     * @template  T of Collection
     *
     * @param  class-string<T>|null     $class
     * @param  array                    $args
     * @param  CursorPaginate|int|null  $paginate
     *
     * @return  Traversable<T>
     */
    public function getIterator(
        ?string $class = null,
        array $args = [],
        CursorPaginate|int|null $paginate = null
    ): \Traversable;

    public function getPaginatedIterator(
        ?string $class = null,
        CursorPaginate|int $perPage = 500,
        array $args = []
    ): PaginateIterator;
}
