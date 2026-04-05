<?php

declare(strict_types=1);

namespace Windwalker\Query\Data;

use Windwalker\Query\Query;

readonly class QueryPaginate
{
    public function __construct(
        public int $length,
        /**
         * @var \Closure(Query $query): ?Query
         */
        public \Closure $nextHandler,
    ) {
    }
}
