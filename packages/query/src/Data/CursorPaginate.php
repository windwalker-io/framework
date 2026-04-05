<?php

declare(strict_types=1);

namespace Windwalker\Query\Data;

use Windwalker\Query\Query;

class CursorPaginate
{
    public \Closure $cursorHandler {
        get => $this->getCursorHandler();
    }

    protected string|\Closure $cursorBy;

    public function __construct(
        public int $length,
        /**
         * @var string|\Closure(Query $query): ?Query
         */
        \Closure|string $cursorBy
    ) {
        $this->cursorBy = $cursorBy;
    }

    protected function getCursorHandler(): \Closure
    {
        $field = $this->cursorBy;

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
