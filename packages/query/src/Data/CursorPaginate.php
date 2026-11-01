<?php

declare(strict_types=1);

namespace Windwalker\Query\Data;

use Windwalker\Query\Query;

/**
 * Cursor (keyset) pagination definition.
 *
 * Columns used by array cursors must make the order unique (put the primary key last),
 * and nullable columns are not supported.
 */
class CursorPaginate
{
    public \Closure $cursorHandler {
        get => $this->getCursorHandler();
    }

    protected string|array|\Closure $cursorBy;

    public function __construct(
        public int $length,
        /**
         * - string: single column, ascending.
         * - array: `['column' => 'ASC|DESC|SortDirection']` or a list of columns (ascending),
         *   the order of the keys is the sort priority.
         * - Closure: `fn(Query $query, array|object $item): ?Query`
         *
         * @var  string|array<string|int, \SortDirection|string>|\Closure
         */
        \Closure|string|array $cursorBy
    ) {
        $this->cursorBy = $cursorBy;
    }

    protected function getCursorHandler(): \Closure
    {
        if ($this->cursorBy instanceof \Closure) {
            return $this->cursorBy;
        }

        $columns = static::normalizeColumns($this->cursorBy);

        return static function (Query $query, object|array $item) use ($columns) {
            $values = [];

            foreach ($columns as $column => $dir) {
                $values[$column] = static::extractValue($item, $column);
            }

            $query->clear('order');

            foreach ($columns as $column => $dir) {
                $query->order($column, $dir);
            }

            return $query->orWhere(static function (Query $query) use ($columns, $values) {
                $equals = [];

                foreach ($columns as $column => $dir) {
                    $conditions = $equals;
                    $conditions[] = [$column, $dir === 'DESC' ? '<' : '>', $values[$column]];

                    $query->where(static function (Query $query) use ($conditions) {
                        foreach ($conditions as $condition) {
                            $query->where(...$condition);
                        }
                    });

                    $equals[] = [$column, '=', $values[$column]];
                }
            });
        };
    }

    /**
     * @return  array<string, 'ASC'|'DESC'>
     */
    protected static function normalizeColumns(string|array $cursorBy): array
    {
        $columns = [];

        foreach ((array) $cursorBy as $column => $dir) {
            if (is_int($column)) {
                $column = $dir;
                $dir = 'ASC';
            }

            if (!is_string($column) || $column === '') {
                throw new \InvalidArgumentException('Cursor column must be a non-empty string.');
            }

            $columns[$column] = static::normalizeDirection($dir);
        }

        if ($columns === []) {
            throw new \InvalidArgumentException('Cursor columns cannot be empty.');
        }

        return $columns;
    }

    protected static function normalizeDirection(mixed $dir): string
    {
        if ($dir instanceof \SortDirection) {
            return $dir === \SortDirection::Descending ? 'DESC' : 'ASC';
        }

        if (is_string($dir)) {
            $upper = strtoupper($dir);

            if ($upper === 'ASC' || $upper === 'DESC') {
                return $upper;
            }
        }

        throw new \InvalidArgumentException('Invalid cursor sort direction.');
    }

    protected static function extractValue(object|array $item, string $column): mixed
    {
        // Strip "alias." prefix and identifier quotes
        $key = trim(substr(strrchr('.' . $column, '.'), 1), '`"[]');

        if (is_array($item)) {
            $exists = array_key_exists($key, $item);
            $value = $item[$key] ?? null;
        } else {
            $exists = property_exists($item, $key) || isset($item->{$key});
            $value = $item->{$key} ?? null;
        }

        if (!$exists) {
            throw new \InvalidArgumentException("Cursor column \"$key\" not found in item.");
        }

        return $value;
    }
}
