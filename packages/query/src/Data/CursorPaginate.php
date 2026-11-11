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
            $conditions = [];

            foreach ($columns as $column => $dir) {
                $conditions[] = [$column, $dir, static::extractValue($item, $column)];
            }

            return static::applyTo($query, $conditions);
        };
    }

    /**
     * Convert an item (object or assoc array) into keyset conditions by the cursor columns.
     *
     * @param  array|object  $item  The last seen row, or the cursor values keyed by column name.
     *
     * Apply the page size and cursor order, plus the keyset condition after the last seen item when given.
     *
     * @param  array|object|null  $item  The last seen row or values, or NULL for the first page.
     *
     * @return  Query  The same query instance.
     */
    public function applyPage(Query $query, array|object|null $item = null): Query
    {
        $query->limit($this->length);

        if ($item !== null) {
            return static::applyTo($query, $this->conditionsFrom($item));
        }

        if ($this->cursorBy instanceof \Closure) {
            return $query;
        }

        $query->clear('order');

        foreach (static::normalizeColumns($this->cursorBy) as $column => $dir) {
            $query->order($column, $dir);
        }

        return $query;
    }

    /**
     * @return  list<array{0: string, 1: string, 2: mixed}>  [column, direction, value]
     */
    public function conditionsFrom(array|object $item): array
    {
        if ($this->cursorBy instanceof \Closure) {
            throw new \InvalidArgumentException('Closure cursor cannot be converted to conditions.');
        }

        $conditions = [];

        foreach (static::normalizeColumns($this->cursorBy) as $column => $dir) {
            $conditions[] = [$column, $dir, static::extractValue($item, $column)];
        }

        return $conditions;
    }

    /**
     * Replace the ORDER BY with the cursor columns and add the keyset condition to the query.
     *
     * @param  list<array{0: string, 1: \SortDirection|string, 2: mixed}>  $conditions  [column, direction, value]
     *
     * @return  Query  The same query instance with cursor order and where applied.
     */
    public static function applyTo(Query $query, array $conditions): Query
    {
        $columns = [];

        foreach ($conditions as $condition) {
            if (count($condition) !== 3) {
                throw new \InvalidArgumentException('Cursor condition must be [column, direction, value].');
            }

            [$column, $dir, $value] = $condition;

            if (!is_string($column) || $column === '') {
                throw new \InvalidArgumentException('Cursor column must be a non-empty string.');
            }

            if ($value === null) {
                throw new \InvalidArgumentException("Cursor value of \"$column\" cannot be null.");
            }

            $columns[] = [$column, static::normalizeDirection($dir), $value];
        }

        if ($columns === []) {
            throw new \InvalidArgumentException('Cursor columns cannot be empty.');
        }

        $query->clear('order');

        foreach ($columns as [$column, $dir]) {
            $query->order($column, $dir);
        }

        if (count($columns) === 1) {
            [$column, $dir, $value] = $columns[0];

            return $query->where($column, $dir === 'DESC' ? '<' : '>', $value);
        }
        return $query->orWhere(static function (Query $query) use ($columns) {
            $equals = [];

            foreach ($columns as [$column, $dir, $value]) {
                $conditions = $equals;
                $conditions[] = [$column, $dir === 'DESC' ? '<' : '>', $value];

                $query->where(static function (Query $query) use ($conditions) {
                    foreach ($conditions as $condition) {
                        $query->where(...$condition);
                    }
                });

                $equals[] = [$column, '=', $value];
            }
        });
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
