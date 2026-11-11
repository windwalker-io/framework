<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

class PaginateIterator implements \IteratorAggregate
{
    public function __construct(
        /**
         * @var  \Closure(int $page, int $perPage): iterable
         */
        public \Closure $callback,
        public \Closure|int $perPage,
        public ?int $limit = null
    ) {}

    public function getIterator(): \Generator
    {
        $page = 1;
        $yielded = 0;

        while ($this->limit === null || $yielded < $this->limit) {
            $count = 0;

            foreach (($this->callback)($page, $this->perPage) as $item) {
                yield $item;
                $yielded++;
                $count++;

                if ($yielded === $this->limit) {
                    return;
                }
            }

            if ($count === 0) {
                break;
            }

            $page++;
        }
    }
}
