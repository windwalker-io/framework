<?php

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\ClausePosition;
use Windwalker\Query\Contract\QueryInterface;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Query\Query;
use Windwalker\Utilities\Iterator\PaginateIterator;

/**
 * Proxies all query building methods to the Query object returned by getQueryBuilder().
 *
 * @psalm-type QueryCallback = \Closure(Query): mixed
 * @psalm-type QueryCallable = callable(Query): mixed
 *
 * @codeCoverageIgnore
 */
trait QueryProxyTrait
{
    abstract public function getQueryBuilder(): Query;

    /**
     * Make query object as SQL string.
     *
     * @return  string
     */
    public function __toString(): string
    {
        return $this->getQueryBuilder()->__toString();
    }

    /**
     * select('foo', 'bar AS b')
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function select(mixed ...$columns): static
    {
        $this->getQueryBuilder()->select(...$columns);

        return $this;
    }

    /**
     * @param  mixed        $column
     * @param  string|null  $alias
     * @param  bool         $isColumn
     *
     * @return  static
     */
    public function selectAs(mixed $column, ?string $alias = null, bool $isColumn = true): static
    {
        $this->getQueryBuilder()->selectAs($column, $alias, $isColumn);

        return $this;
    }

    /**
     * @param  string|array  $column
     * @param  mixed         ...$args
     *
     * @return  static
     */
    public function selectRaw(mixed $column, mixed ...$args): static
    {
        $this->getQueryBuilder()->selectRaw($column, ...$args);

        return $this;
    }

    /**
     * from
     *
     * @param  string|array|Query|QueryCallback  $tables
     * @param  string|null                       $alias
     *
     * @return  static
     */
    public function from(mixed $tables, ?string $alias = null): static
    {
        $this->getQueryBuilder()->from($tables, $alias);

        return $this;
    }

    /**
     * @param  string                                      $type
     * @param  string|Query|ClauseInterface|QueryCallback  $table
     * @param  string|null                                 $alias
     * @param  array                                       $on
     *
     * @return  static
     */
    public function join(string $type, mixed $table, ?string $alias = null, mixed ...$on): static
    {
        $this->getQueryBuilder()->join($type, $table, $alias, ...$on);

        return $this;
    }

    /**
     * @param  string                                      $type
     * @param  string|Query|ClauseInterface|QueryCallback  $table
     * @param  string|null                                 $alias
     * @param  array                                       $on
     *
     * @return  static
     */
    public function joinLateral(string $type, mixed $table, ?string $alias = null, mixed ...$on): static
    {
        $this->getQueryBuilder()->joinLateral($type, $table, $alias, ...$on);

        return $this;
    }

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
    public function as(mixed $value, mixed $alias = null, bool $isColumn = true): AsClause
    {
        return $this->getQueryBuilder()->as($value, $alias, $isColumn);
    }

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
    public function union(mixed $query, string $type = ''): static
    {
        $this->getQueryBuilder()->union($query, $type);

        return $this;
    }

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
    public function unionDistinct(mixed $query): static
    {
        $this->getQueryBuilder()->unionDistinct($query);

        return $this;
    }

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
    public function unionAll(mixed $query): mixed
    {
        return $this->getQueryBuilder()->unionAll($query);
    }

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
    ): static {
        $this->getQueryBuilder()->order($column, $dir, $pos);

        return $this;
    }

    public function orderByRandom(ClausePosition $pos = ClausePosition::APPEND): static
    {
        $this->getQueryBuilder()->orderByRandom($pos);

        return $this;
    }

    public function orderByField(
        mixed $column,
        array $values,
        \SortDirection|string|null $dir = null,
        ClausePosition $pos = ClausePosition::APPEND
    ): static {
        $this->getQueryBuilder()->orderByField($column, $values, $dir, $pos);

        return $this;
    }

    public function orderRaw(Clause|string $order, mixed ...$args): static
    {
        $this->getQueryBuilder()->orderRaw($order, ...$args);

        return $this;
    }

    /**
     * @param  string|array  ...$columns
     *
     * @return  static
     */
    public function group(mixed ...$columns): static
    {
        $this->getQueryBuilder()->group(...$columns);

        return $this;
    }

    public function groupRaw(Clause|string $group, mixed ...$args): static
    {
        $this->getQueryBuilder()->groupRaw($group, ...$args);

        return $this;
    }

    /**
     * @param  int|null  $limit
     *
     * @return  static
     */
    public function limit(?int $limit): static
    {
        $this->getQueryBuilder()->limit($limit);

        return $this;
    }

    /**
     * @param  int|null  $offset
     *
     * @return  static
     */
    public function offset(?int $offset): static
    {
        $this->getQueryBuilder()->offset($offset);

        return $this;
    }

    /**
     * @param  string  $table
     * @param  bool    $incrementField
     *
     * @return  static
     */
    public function insert(string $table, bool $incrementField = false): static
    {
        $this->getQueryBuilder()->insert($table, $incrementField);

        return $this;
    }

    /**
     * @param  string       $table
     * @param  string|null  $alias
     *
     * @return  static
     */
    public function update(string $table, ?string $alias = null): static
    {
        $this->getQueryBuilder()->update($table, $alias);

        return $this;
    }

    public function delete(string $table, ?string $alias = null): static
    {
        $this->getQueryBuilder()->delete($table, $alias);

        return $this;
    }

    /**
     * columns
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function columns(mixed ...$columns): static
    {
        $this->getQueryBuilder()->columns(...$columns);

        return $this;
    }

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
    public function values(Query|array ...$valuesSet): static
    {
        $this->getQueryBuilder()->values(...$valuesSet);

        return $this;
    }

    public function set(string|iterable $column, mixed $value = null): static
    {
        $this->getQueryBuilder()->set($column, $value);

        return $this;
    }

    public function setRaw(Clause|string $value, mixed ...$args): static
    {
        $this->getQueryBuilder()->setRaw($value, ...$args);

        return $this;
    }

    /**
     * alias
     *
     * @param  string  $alias
     *
     * @return  static
     */
    public function alias(string $alias): static
    {
        $this->getQueryBuilder()->alias($alias);

        return $this;
    }

    public function prefix(ClauseInterface|array|string $prefix, mixed ...$args): static
    {
        $this->getQueryBuilder()->prefix($prefix, ...$args);

        return $this;
    }

    public function suffix(ClauseInterface|array|string $suffix, mixed ...$args): static
    {
        $this->getQueryBuilder()->suffix($suffix, ...$args);

        return $this;
    }

    /**
     * Add FOR UPDATE after query string.
     *
     * @param  string       $for
     * @param  string|null  $do
     *
     * @return  static
     */
    public function rowLock(string $for = 'UPDATE', ?string $do = null): static
    {
        $this->getQueryBuilder()->rowLock($for, $do);

        return $this;
    }

    /**
     * forUpdate
     *
     * @param  string|null  $do
     *
     * @return  static
     */
    public function forUpdate(?string $do = null): static
    {
        $this->getQueryBuilder()->forUpdate($do);

        return $this;
    }

    /**
     * forShare
     *
     * @param  string|null  $do
     *
     * @return  static
     */
    public function forShare(?string $do = null): static
    {
        $this->getQueryBuilder()->forShare($do);

        return $this;
    }

    /**
     * sql
     *
     * @param  string  $sql
     * @param  mixed   ...$args
     *
     * @return  static
     */
    public function sql(string $sql, mixed ...$args): static
    {
        $this->getQueryBuilder()->sql($sql, ...$args);

        return $this;
    }

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
    public function format(string $format, mixed ...$args): string
    {
        return $this->getQueryBuilder()->format($format, ...$args);
    }

    public function render(bool $emulatePrepared = false, ?array &$bounded = null): string
    {
        return $this->getQueryBuilder()->render($emulatePrepared, $bounded);
    }

    /**
     * @param  bool|\Closure  $pre
     * @param  bool           $format
     * @param  bool           $asString
     *
     * @return  mixed|static
     */
    public function debug(\Closure|bool $pre = false, bool $format = true, bool $asString = false): mixed
    {
        return $this->getQueryBuilder()->debug($pre, $format, $asString);
    }

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
    public function mustGet(?string $class = null, array $args = []): object
    {
        return $this->getQueryBuilder()->mustGet($class, $args);
    }

    public function mustGetResult(): mixed
    {
        return $this->getQueryBuilder()->mustGetResult();
    }

    /**
     * @return  static
     */
    public function createSubQuery(): QueryInterface
    {
        return $this->getQueryBuilder()->createSubQuery();
    }

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param  string|array  $clauses  Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return  static  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear(mixed ...$clauses): static
    {
        $this->getQueryBuilder()->clear(...$clauses);

        return $this;
    }

    public function prepareStatement(): StatementInterface
    {
        return $this->getQueryBuilder()->prepareStatement();
    }

    /**
     * @return  string|null
     *
     * @codeCoverageIgnore
     */
    public function getDefaultItemClass(): ?string
    {
        return $this->getQueryBuilder()->getDefaultItemClass();
    }

    /**
     * @param  string|null  $defaultItemClass
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultItemClass(?string $defaultItemClass): static
    {
        $this->getQueryBuilder()->setDefaultItemClass($defaultItemClass);

        return $this;
    }

    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key            The key that will be used in your SQL query to reference the value.
     *                                               Usually of the form ':key', but can also be an integer.
     * @param  mixed                 &$value         The value that will be bound. The value is passed by reference to
     *                                               support output parameters such as those possible with stored
     *                                               procedures.
     * @param  mixed                 $dataType       Constant corresponding to a SQL datatype.
     * @param  integer               $length         The length of the variable. Usually required for OUTPUT parameters.
     * @param  array                 $driverOptions  Optional driver options to be used.
     *
     * @return  static
     *
     * @since   3.5.5
     */
    public function bindParam(
        string|int|array|null $key = null,
        mixed &$value = null,
        mixed $dataType = null,
        int $length = 0,
        mixed $driverOptions = null
    ): static {
        $this->getQueryBuilder()->bindParam($key, $value, $dataType, $length, $driverOptions);

        return $this;
    }

    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key       The key that will be used in your SQL query to reference the
     *                                          value. Usually of the form ':key', but can also be an integer.
     * @param  mixed                 &$value    The value that will be bound. The value is passed by reference to
     *                                          support output parameters such as those possible with stored
     *                                          procedures.
     * @param  mixed                 $dataType  Constant corresponding to a SQL datatype.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function bind(
        string|int|array|null $key = null,
        mixed $value = null,
        mixed $dataType = null
    ): static {
        $this->getQueryBuilder()->bind($key, $value, $dataType);

        return $this;
    }

    /**
     * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then
     * that item is returned.
     *
     * @param  mixed  $key  The bounded variable key to retrieve.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    public function getBounded(mixed $key = null): ?array
    {
        return $this->getQueryBuilder()->getBounded($key);
    }

    /**
     * @return  static
     *
     * @codeCoverageIgnore
     */
    public function resetBounded(): static
    {
        $this->getQueryBuilder()->resetBounded();

        return $this;
    }

    /**
     * unbind
     *
     * @param  string|array  $keys
     *
     * @return  static
     */
    public function unbind(mixed $keys): static
    {
        $this->getQueryBuilder()->unbind($keys);

        return $this;
    }

    /**
     * @param  string|array|QueryCallback|ClauseInterface  $column   Column name, array where list or callback
     *                                                               function as sub query.
     * @param  mixed                                       ...$args
     *
     * @return  static
     */
    public function where(mixed $column, mixed ...$args): static
    {
        $this->getQueryBuilder()->where($column, ...$args);

        return $this;
    }

    /**
     * whereRaw
     *
     * @param  string|Clause  $string
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function whereRaw(Clause|string $string, mixed ...$args): static
    {
        $this->getQueryBuilder()->whereRaw($string, ...$args);

        return $this;
    }

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function orWhere(\Closure|array $wheres): static
    {
        $this->getQueryBuilder()->orWhere($wheres);

        return $this;
    }

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function andWhere(\Closure|array $wheres): static
    {
        $this->getQueryBuilder()->andWhere($wheres);

        return $this;
    }

    /**
     * @param  string|array|QueryCallback|ClauseInterface  $column   Column name, array where list or callback
     *                                                               function as sub query.
     * @param  mixed                                       ...$args
     *
     * @return  static
     */
    public function having(mixed $column, mixed ...$args): static
    {
        $this->getQueryBuilder()->having($column, ...$args);

        return $this;
    }

    /**
     * @param  string|Clause  $string
     * @param  array          ...$args
     *
     * @return  static
     */
    public function havingRaw(mixed $string, mixed ...$args): static
    {
        $this->getQueryBuilder()->havingRaw($string, ...$args);

        return $this;
    }

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function orHaving(\Closure|array $wheres): static
    {
        $this->getQueryBuilder()->orHaving($wheres);

        return $this;
    }

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function whereExists(Query|callable $conditions): static
    {
        $this->getQueryBuilder()->whereExists($conditions);

        return $this;
    }

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function whereNotExists(Query|callable $conditions): static
    {
        $this->getQueryBuilder()->whereNotExists($conditions);

        return $this;
    }

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function havingExists(Query|callable $conditions): static
    {
        $this->getQueryBuilder()->havingExists($conditions);

        return $this;
    }

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function havingNotExists(Query|callable $conditions): static
    {
        $this->getQueryBuilder()->havingNotExists($conditions);

        return $this;
    }

    /**
     * @param  string  $expr
     * @param  bool    $instant
     *
     * @return  Clause
     *
     * @since  3.5.21
     */
    public function jsonSelector(string $expr, bool $instant = false): Clause
    {
        return $this->getQueryBuilder()->jsonSelector($expr, $instant);
    }

    /**
     * @param  string  $expr
     * @param  mixed   $value
     *
     * @return  $this
     *
     * @throws  \JsonException
     *
     * @deprecated  Use {@see whereJsonContainsAll()} instead.
     *               In the next major release, whereJsonContainsAll() will be renamed back to whereJsonContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function whereJsonContains(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->whereJsonContains($expr, $value);

        return $this;
    }

    public function whereJsonContainsAll(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->whereJsonContainsAll($expr, $value);

        return $this;
    }

    /**
     * @deprecated  Use {@see whereJsonNotContainsAll()} instead.
     *               In the next major release, whereJsonNotContainsAll() will be renamed back to whereJsonNotContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function whereJsonNotContains(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->whereJsonNotContains($expr, $value);

        return $this;
    }

    public function whereJsonNotContainsAll(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->whereJsonNotContainsAll($expr, $value);

        return $this;
    }

    /**
     * @deprecated  Use {@see havingJsonContainsAll()} instead.
     *               In the next major release, havingJsonContainsAll() will be renamed back to havingJsonContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function havingJsonContains(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->havingJsonContains($expr, $value);

        return $this;
    }

    public function havingJsonContainsAll(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->havingJsonContainsAll($expr, $value);

        return $this;
    }

    /**
     * @deprecated  Use {@see havingJsonNotContainsAll()} instead.
     *               In the next major release, havingJsonNotContainsAll() will be renamed back to havingJsonNotContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function havingJsonNotContains(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->havingJsonNotContains($expr, $value);

        return $this;
    }

    public function havingJsonNotContainsAll(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->havingJsonNotContainsAll($expr, $value);

        return $this;
    }

    public function selectJsonLength(string $expr, string $as = ''): static
    {
        $this->getQueryBuilder()->selectJsonLength($expr, $as);

        return $this;
    }

    public function whereJsonLength(array|string $expr, mixed ...$args): static
    {
        $this->getQueryBuilder()->whereJsonLength($expr, ...$args);

        return $this;
    }

    public function whereJsonExists(string $expr): static
    {
        $this->getQueryBuilder()->whereJsonExists($expr);

        return $this;
    }

    public function whereJsonNotExists(string $expr): static
    {
        $this->getQueryBuilder()->whereJsonNotExists($expr);

        return $this;
    }

    public function havingJsonExists(string $expr): static
    {
        $this->getQueryBuilder()->havingJsonExists($expr);

        return $this;
    }

    public function havingJsonNotExists(string $expr): static
    {
        $this->getQueryBuilder()->havingJsonNotExists($expr);

        return $this;
    }

    public function setJson(string $expr, mixed $value): static
    {
        $this->getQueryBuilder()->setJson($expr, $value);

        return $this;
    }

    public function setJsonRemove(string $expr): static
    {
        $this->getQueryBuilder()->setJsonRemove($expr);

        return $this;
    }

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
     * @return  \Generator<T>
     */
    public function iterateBatched(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator
    {
        return $this->getQueryBuilder()->iterateBatched($paginate, $class, $args);
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
     * @return  \Generator<Collection<T>>
     */
    public function iterateChunks(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator
    {
        return $this->getQueryBuilder()->iterateChunks($paginate, $class, $args);
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
     * @return  \Generator<T>
     */
    public function iterateWhile(int $length, ?string $class = null, array $args = []): \Generator
    {
        return $this->getQueryBuilder()->iterateWhile($length, $class, $args);
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
     * @return  \Generator<T>
     */
    public function iterateBatchWhile(int $length, ?string $class = null, array $args = []): \Generator
    {
        return $this->getQueryBuilder()->iterateBatchWhile($length, $class, $args);
    }

    /**
     * @template  T of Collection
     *
     * @param  class-string<T>|null     $class
     * @param  array                    $args
     * @param  CursorPaginate|int|null  $paginate
     *
     * @return  \Traversable<T>
     */
    public function getIterator(
        ?string $class = null,
        array $args = [],
        CursorPaginate|int|null $paginate = null
    ): \Traversable {
        return $this->getQueryBuilder()->getIterator($class, $args, $paginate);
    }

    public function getPaginatedIterator(
        ?string $class = null,
        CursorPaginate|int $perPage = 500,
        array $args = []
    ): PaginateIterator {
        return $this->getQueryBuilder()->getPaginatedIterator($class, $perPage, $args);
    }
}
