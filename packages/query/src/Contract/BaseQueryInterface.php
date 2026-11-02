<?php

declare(strict_types=1);

namespace Windwalker\Query\Contract;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\ClausePosition;
use Windwalker\Query\Query;

/**
 * Basic query building methods.
 *
 * @psalm-type QueryCallback = \Closure(Query): mixed
 * @psalm-type QueryCallable = callable(Query): mixed
 */
interface BaseQueryInterface
{
    /**
     * Make query object as SQL string.
     *
     * @return  string
     */
    public function __toString(): string;

    /**
     * select('foo', 'bar AS b')
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function select(mixed ...$columns): static;

    /**
     * @param  mixed        $column
     * @param  string|null  $alias
     * @param  bool         $isColumn
     *
     * @return  static
     */
    public function selectAs(mixed $column, ?string $alias = null, bool $isColumn = true): static;

    /**
     * @param  string|array  $column
     * @param  mixed         ...$args
     *
     * @return  static
     */
    public function selectRaw(mixed $column, mixed ...$args): static;

    /**
     * from
     *
     * @param  string|array|Query|QueryCallback  $tables
     * @param  string|null                       $alias
     *
     * @return  static
     */
    public function from(mixed $tables, ?string $alias = null): static;

    /**
     * @param  string                                      $type
     * @param  string|Query|ClauseInterface|QueryCallback  $table
     * @param  string|null                                 $alias
     * @param  array                                       $on
     *
     * @return  static
     */
    public function join(string $type, mixed $table, ?string $alias = null, mixed ...$on): static;

    /**
     * @param  string                                      $type
     * @param  string|Query|ClauseInterface|QueryCallback  $table
     * @param  string|null                                 $alias
     * @param  array                                       $on
     *
     * @return  static
     */
    public function joinLateral(string $type, mixed $table, ?string $alias = null, mixed ...$on): static;

    /**
     * Handle column and sub query.
     *
     * @param  string|array|Query  $value     The column or sub query object.
     * @param  string|bool|null    $alias     The alias string, if this arg provided, will override sub query
     *                                        self-contained alias, if is FALSE, will force ignore alias
     *                                        from aub query.
     * @param  bool                $isColumn  Quote value as column or string.
     *
     * @return  AsClause
     */
    public function as(mixed $value, mixed $alias = null, bool $isColumn = true): AsClause;

    /**
     * Add a query to UNION with the current query.
     * Multiple unions each require separate statements and create an array of unions.
     *
     * @param  mixed   $query  The Query object or string to union.
     * @param  string  $type   The union type, can be `DISTINCT` or `ALL`, default is empty.
     *
     * @return  static  The Query object on success or boolean false on failure.
     *
     * @since   2.0
     */
    public function union(mixed $query, string $type = ''): static;

    /**
     * Add a query to UNION DISTINCT with the current query. Simply a proxy to Union with the Distinct clause.
     *
     * Usage:
     * $query->unionDistinct('SELECT name FROM  #__foo')
     *
     * @param  mixed  $query  The Query object or string to union.
     *
     * @return  static  The Query object on success or boolean false on failure.
     *
     * @since   2.0
     */
    public function unionDistinct(mixed $query): static;

    /**
     * Add a query to UNION ALL with the current query.
     * Multiple unions each require separate statements and create an array of unions.
     *
     * Usage:
     * $query->unionAll('SELECT name FROM  #__foo')
     * $query->unionAll(array('SELECT name FROM  #__foo','SELECT name FROM  #__bar'))
     *
     * @param  mixed  $query  The Query object or string to union.
     *
     * @return  mixed  The Query object on success or boolean false on failure.
     *
     * @see     union
     *
     * @since   2.0
     */
    public function unionAll(mixed $query): mixed;

    /**
     * @param  array|string|ClauseInterface  $column
     * @param  \SortDirection|string|null    $dir
     * @param  ClausePosition                $pos
     *
     * @return  static
     */
    public function order(
        mixed $column,
        \SortDirection|string|null $dir = null,
        ClausePosition $pos = ClausePosition::APPEND
    ): static;

    public function orderByRandom(ClausePosition $pos = ClausePosition::APPEND): static;

    public function orderByField(
        mixed $column,
        array $values,
        \SortDirection|string|null $dir = null,
        ClausePosition $pos = ClausePosition::APPEND
    ): static;

    public function orderRaw(Clause|string $order, mixed ...$args): static;

    /**
     * @param  string|array  ...$columns
     *
     * @return  static
     */
    public function group(mixed ...$columns): static;

    public function groupRaw(Clause|string $group, mixed ...$args): static;

    /**
     * @param  int|null  $limit
     *
     * @return  static
     */
    public function limit(?int $limit): static;

    /**
     * @param  int|null  $offset
     *
     * @return  static
     */
    public function offset(?int $offset): static;

    /**
     * @param  string  $table
     * @param  bool    $incrementField
     *
     * @return  static
     */
    public function insert(string $table, bool $incrementField = false): static;

    /**
     * @param  string       $table
     * @param  string|null  $alias
     *
     * @return  static
     */
    public function update(string $table, ?string $alias = null): static;

    public function delete(string $table, ?string $alias = null): static;

    /**
     * columns
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function columns(mixed ...$columns): static;

    /**
     * Every row must be a single array.
     *
     * ```php
     * ->values([...row1], [...row2], [...row3], ...)
     * ```
     *
     * @param  mixed  ...$valuesSet
     *
     * @return  static
     */
    public function values(Query|array ...$valuesSet): static;

    public function set(string|iterable $column, mixed $value = null): static;

    public function setRaw(Clause|string $value, mixed ...$args): static;

    /**
     * alias
     *
     * @param  string  $alias
     *
     * @return  static
     */
    public function alias(string $alias): static;

    public function prefix(ClauseInterface|array|string $prefix, mixed ...$args): static;

    public function suffix(ClauseInterface|array|string $suffix, mixed ...$args): static;

    /**
     * Add FOR UPDATE after query string.
     *
     * @param  string       $for
     * @param  string|null  $do
     *
     * @return  static
     */
    public function rowLock(string $for = 'UPDATE', ?string $do = null): static;

    /**
     * forUpdate
     *
     * @param  string|null  $do
     *
     * @return  static
     */
    public function forUpdate(?string $do = null): static;

    /**
     * forShare
     *
     * @param  string|null  $do
     *
     * @return  static
     */
    public function forShare(?string $do = null): static;

    /**
     * sql
     *
     * @param  string  $sql
     * @param  mixed   ...$args
     *
     * @return  static
     */
    public function sql(string $sql, mixed ...$args): static;

    /**
     * Find and replace sprintf-like tokens in a format string.
     * Each token takes one of the following forms:
     *     %%       - A literal percent character.
     *     %[t]     - Where [t] is a type specifier.
     *     %[n]$[x] - Where [n] is an argument specifier and [t] is a type specifier.
     *
     * Types:
     * a - Numeric: Replacement text is coerced to a numeric type but not quoted or escaped.
     * e - Escape: Replacement text is passed to $this->escape().
     * E - Escape (extra): Replacement text is passed to $this->escape() with true as the second argument.
     * n - Name Quote: Replacement text is passed to $this->quoteName().
     * q - Quote: Replacement text is passed to $this->quote().
     * Q - Quote (no escape): Replacement text is passed to $this->quote() with false as the second argument.
     * r - Raw: Replacement text is used as-is. (Be careful)
     *
     * Date Types:
     * - Replacement text automatically quoted (use uppercase for Name Quote).
     * - Replacement text should be a string in date format or name of a date column.
     * y/Y - Year
     * m/M - Month
     * d/D - Day
     * h/H - Hour
     * i/I - Minute
     * s/S - Second
     *
     * Invariable Types:
     * - Takes no argument.
     * - Argument index not incremented.
     * t - Replacement text is the result of $this->currentTimestamp().
     * z - Replacement text is the result of $this->nullDate(false).
     * Z - Replacement text is the result of $this->nullDate(true).
     *
     * Usage:
     * $query->format('SELECT %1$n FROM %2$n WHERE %3$n = %4$a', 'foo', '#__foo', 'bar', 1);
     * Returns: SELECT `foo` FROM `#__foo` WHERE `bar` = 1
     *
     * Notes:
     * The argument specifier is optional but recommended for clarity.
     * The argument index used for unspecified tokens is incremented only when used.
     *
     * @param  string  $format  The formatting string.
     * @param  array   $args    The strings variables.
     *
     * @return  string  Returns a string produced according to the formatting string.
     *
     * @note    This method is a modified version from Joomla DatabaseQuery.
     *
     * @since   2.0
     */
    public function format(string $format, mixed ...$args): string;

    public function render(bool $emulatePrepared = false, ?array &$bounded = null): string;

    /**
     * @param  bool|\Closure  $pre
     * @param  bool           $format
     * @param  bool           $asString
     *
     * @return  mixed|static
     */
    public function debug(\Closure|bool $pre = false, bool $format = true, bool $asString = false): mixed;

    /**
     * @param  string|null  $class
     * @param  array        $args
     *
     * @return  object|Collection
     *
     * @psalm-template E
     * @psalm-param class-string<E> $class
     * @psalm-return E
     */
    public function mustGet(?string $class = null, array $args = []): object;

    public function mustGetResult(): mixed;

    /**
     * @return  static
     */
    public function createSubQuery(): QueryInterface;

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param  string|array  $clauses  Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return  static  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear(mixed ...$clauses): static;

    public function prepareStatement(): StatementInterface;

    /**
     * @return  string|null
     *
     * @codeCoverageIgnore
     */
    public function getDefaultItemClass(): ?string;

    /**
     * @param  string|null  $defaultItemClass
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultItemClass(?string $defaultItemClass): static;
}
