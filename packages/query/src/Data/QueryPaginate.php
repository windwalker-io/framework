<?php

declare(strict_types=1);

namespace Windwalker\Query\Data;

use Windwalker\Query\Query;

class QueryPaginate
{
    public \Closure $nextHandler {
        get => $this->getNextHandler();
    }

    protected string|\Closure $cursorField;

    public function __construct(
        public int $length,
        /**
         * @var string|\Closure(Query $query): ?Query
         */
        \Closure|string $cursorField
    ) {
        $this->cursorField = $cursorField;
    }

    protected function getNextHandler(): \Closure
    {
        $field = $this->cursorField;

        if (is_string($field)) {
            $field = static function (Query $query, object|array $item) use ($field) {
                if (is_array($item)) {
                    $cursor = $item[$field];
                } else {
                    $cursor = $item->{$field};
                }

                return $query->where($field, '>', $cursor);
            };
        }

        return $field;
    }
}
