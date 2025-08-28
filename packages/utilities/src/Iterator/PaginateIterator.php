<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

class PaginateIterator implements \IteratorAggregate
{
    public function __construct(
        /**
         * @var \Closure(int $page, int $perPage): iterable
         */
        public \Closure $callback,
        public int $perPage,
        public ?int $limit = null
    ) {
    }

    public function getIterator(): \Generator
    {
        $page = 1;
        $remain = $this->limit;

        while (true) {
            if ($remain !== null) {
                $remain -= $this->perPage;
            }

            $count = 0;

            $items = ($this->callback)($page, $this->perPage);

            foreach ($items as $item) {
                yield $item;
                $count++;
            }

            if ($count === 0) {
                break;
            }

            if ($remain !== null && $remain <= 0) {
                break;
            }

            $page++;
        }
    }
}
