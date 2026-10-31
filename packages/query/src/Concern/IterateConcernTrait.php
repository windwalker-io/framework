<?php

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Generator;
use Traversable;
use Windwalker\Data\Collection;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Utilities\Iterator\PaginateIterator;

/**
 * Trait IterateConcernTrait
 *
 * Offset, cursor and while based iterate methods for Query.
 */
trait IterateConcernTrait
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
    public function iterateBatched(CursorPaginate|int $paginate, ?string $class = null, array $args = []): Generator
    {
        $length = $paginate instanceof CursorPaginate ? $paginate->length : $paginate;
        $cursorHandler = $paginate instanceof CursorPaginate ? $paginate->cursorHandler : null;
        $offset = $this->getOffset() ?? 0;
        $first = true;
        $lastItem = null;
        $max = $this->getLimit();

        $yielded = 0;

        while (true) {
            if ($max !== null && $yielded >= $max) {
                break;
            }

            $query = clone $this;

            if (!$cursorHandler || $first) {
                $query->offset($offset);
                $first = false;
            }

            $currentLength = $max !== null
                ? min($length, $max - $yielded)
                : $length;

            $query->limit($currentLength);

            if ($cursorHandler && !$first && $lastItem) {
                $query = $cursorHandler($query, $lastItem) ?? $query;
            }

            $items = $query->all($class, $args);

            $count = 0;

            foreach ($items as $item) {
                if ($max !== null && $yielded >= $max) {
                    break;
                }

                $count++;
                $yielded++;
                yield $item;

                $lastItem = $item;
            }

            if ($count === 0 || $count < $currentLength) {
                break;
            }

            $offset += $length;
        }
    }

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
    public function iterateChunks(CursorPaginate|int $paginate, ?string $class = null, array $args = []): Generator
    {
        $length = $paginate instanceof CursorPaginate ? $paginate->length : $paginate;
        $cursorHandler = $paginate instanceof CursorPaginate ? $paginate->cursorHandler : null;
        $offset = $this->getOffset() ?? 0;
        $first = true;
        $lastItem = null;
        $max = $this->getLimit();
        $yielded = 0;

        while (true) {
            if ($max !== null && $yielded >= $max) {
                break;
            }

            $query = clone $this;

            if (!$cursorHandler || $first) {
                $query->offset($offset);
                $first = false;
            }

            $currentLength = $max !== null
                ? min($length, $max - $yielded)
                : $length;

            $query->limit($currentLength);

            if ($cursorHandler && !$first && $lastItem) {
                $query = $cursorHandler($query, $lastItem) ?? $query;
            }

            $items = $query->all($class, $args);

            if (count($items) === 0) {
                break;
            }

            if ($cursorHandler) {
                $runner = static function () use ($items, &$lastItem, &$yielded, $max) {
                    foreach ($items as $item) {
                        if ($max !== null && $yielded >= $max) {
                            break;
                        }

                        $yielded++;
                        yield $item;

                        $lastItem = $item;
                    }
                };

                yield $runner();
            } else {
                if ($max !== null) {
                    $items = $items->slice(0, max(0, $max - $yielded));
                }

                $yielded += count($items);
                yield $items;
            }

            if (count($items) < $currentLength) {
                break;
            }

            $offset += $length;
        }
    }

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
    public function iterateWhile(int $length, ?string $class = null, array $args = []): Generator
    {
        $max = $this->getLimit();
        $yielded = 0;
        $query = clone $this;
        $query->offset(0)->limit($length);

        while (true) {
            if ($max !== null && $yielded >= $max) {
                break;
            }

            $currentLength = $max !== null
                ? min($length, $max - $yielded)
                : $length;

            $query->limit($currentLength);
            $items = $query->getIterator($class, $args);

            $count = 0;

            foreach ($items as $item) {
                if ($max !== null && $yielded >= $max) {
                    break;
                }

                $count++;
                $yielded++;
                yield $item;
            }

            if ($count === 0 || $count < $currentLength) {
                break;
            }
        }
    }

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
    public function iterateBatchWhile(int $length, ?string $class = null, array $args = []): Generator
    {
        $max = $this->getLimit();
        $yielded = 0;
        $query = clone $this;
        $query->offset(0)->limit($length);

        while (true) {
            if ($max !== null && $yielded >= $max) {
                break;
            }

            $currentLength = $max !== null
                ? min($length, $max - $yielded)
                : $length;

            $query->limit($currentLength);
            $items = $query->all($class, $args);

            $count = 0;

            foreach ($items as $item) {
                if ($max !== null && $yielded >= $max) {
                    break;
                }

                $count++;
                $yielded++;
                yield $item;
            }

            if ($count === 0 || $count < $currentLength) {
                break;
            }
        }
    }

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
    ): Traversable {
        if ($paginate !== null) {
            return $this->getPaginatedIterator($class, $paginate, $args);
        }

        return $this->prepareStatement()->getIterator($class, $args);
    }

    public function getPaginatedIterator(
        ?string $class = null,
        CursorPaginate|int $perPage = 500,
        array $args = []
    ): PaginateIterator {
        $offset = $this->getOffset() ?? 0;
        $length = $perPage instanceof CursorPaginate ? $perPage->length : $perPage;
        $cursorHandler = $perPage instanceof CursorPaginate ? $perPage->cursorHandler : null;
        $first = true;
        $lastItem = null;
        $max = $this->getLimit();
        $yielded = 0;

        return new PaginateIterator(
            function (
                int $page,
                int $length
            ) use (
                $args,
                $class,
                &$offset,
                &$first,
                &$lastItem,
                $cursorHandler,
                $max,
                &$yielded
            ) {
                if ($max !== null && $yielded >= $max) {
                    return;
                }

                $query = clone $this;

                if (!$cursorHandler || $first) {
                    $query->offset($offset);
                    $first = false;
                }

                $currentLength = $max !== null
                    ? min($length, $max - $yielded)
                    : $length;

                $query->limit($currentLength);

                if ($cursorHandler && !$first && $lastItem) {
                    $query = $cursorHandler($query, $lastItem) ?? $query;
                }

                foreach ($query->getIterator($class, $args) as $item) {
                    if ($max !== null && $yielded >= $max) {
                        break;
                    }

                    $yielded++;
                    yield $item;

                    $lastItem = $item;
                }

                $offset += $length;
            },
            $length,
            $this->getLimit()
        );
    }
}
