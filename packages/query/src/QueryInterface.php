<?php

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Clause\AlterClause;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\ClausePosition;
use Windwalker\Query\Clause\ExprClause;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Query\Expression\Expression;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Utilities\Iterator\PaginateIterator;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The public API of Query, so it can be proxied by other objects.
 */
interface QueryInterface
{
    /**
     * Make query object as SQL string.
     *
     * @return  string
     */
    public function __toString(): string;

    public function getName(): string;

    /**
     * select('foo', 'bar AS b')
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function select(...$columns): static;

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
    public function selectRaw(mixed $column, ...$args): static;

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
    public function join(string $type, mixed $table, ?string $alias = null, ...$on): static;

    /**
     * @param  string                                      $type
     * @param  string|Query|ClauseInterface|QueryCallback  $table
     * @param  string|null                                 $alias
     * @param  array                                       $on
     *
     * @return  static
     */
    public function joinLateral(string $type, mixed $table, ?string $alias = null, ...$on): static;

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

    public function orderRaw(Clause|string $order, mixed ...$args): static;

    /**
     * @param  string|array  ...$columns
     *
     * @return  static
     */
    public function group(...$columns): static;

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
    public function columns(...$columns): static;

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

    public function set(\Traversable|array|string $column, mixed $value = null): static;

    public function setRaw(Clause|string $value, ...$args): static;

    /**
     * @param  string        $name
     * @param  array|string  $elements
     * @param  string        $glue
     *
     * @return  Clause
     */
    public function clause(string $name, mixed $elements = [], string $glue = ' '): Clause;

    public function expr(string $name, ...$elements): ExprClause;

    public function alter(string $target, string $targetName): AlterClause;

    /**
     * escape
     *
     * @param  string|iterable|WrapperInterface  $value
     *
     * @return  string|array
     */
    public function escape(mixed $value): array|string;

    /**
     * quote
     *
     * @param  mixed|WrapperInterface  $value
     *
     * @return  string
     */
    public function quote(mixed $value): mixed;

    /**
     * Make a value as quoted string or bounded.
     *
     * @param  mixed  $value
     * @param  bool   $instant
     *
     * @return  mixed
     *
     * @internal Do not use this method directly.
     */
    public function valueize(mixed $value, bool $instant): mixed;

    /**
     * quoteName
     *
     * @param  string|Stringable  $name
     * @param  int                $flags
     *
     * @return  mixed
     */
    public function quoteName(\Stringable|string $name, int $flags = 0): Clause|string;

    /**
     * Resolve column name,
     * - If is RawWrapper, return raw value.
     * - If is pure name string, wrap with name quote.
     * - If contains arrow sign, convert to json selector.
     *
     * @param  string|Stringable  $name
     * @param  int                $flags
     *
     * @return  string
     */
    public function resolveColumn(\Stringable|string $name, int $flags = 0): string;

    public function qnMultiple(\Traversable|Clause|WrapperInterface|array $names, int $flags = 0): mixed;

    /**
     * alias
     *
     * @param  string  $alias
     *
     * @return  static
     */
    public function alias(string $alias): static;

    public function prefix(ClauseInterface|array|string $prefix, ...$args): static;

    public function suffix(ClauseInterface|array|string $suffix, ...$args): static;

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
     * @param  array|QueryCallable  $values
     *
     * @return  $this
     */
    public function onDuplicateKeyUpdate(callable|array $values): static;

    /**
     * sql
     *
     * @param  string  $sql
     * @param  mixed   ...$args
     *
     * @return  static
     */
    public function sql(string $sql, ...$args): static;

    public function nullDate(): string;

    public function getDateFormat(): string;

    /**
     * castValue
     *
     * @param  mixed  $value
     *
     * @return  string
     */
    public function castValue(mixed $value): mixed;

    public function raw(string $string, ...$args): RawWrapper;

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
    public function format(string $format, ...$args): string;

    public function render(bool $emulatePrepared = false, ?array &$bounded = null): string;

    /**
     * debug
     *
     * @param  bool|\Closure  $pre
     * @param  bool           $format
     * @param  bool           $asString
     *
     * @return  mixed|static
     */
    public function debug(\Closure|bool $pre = false, bool $format = true, bool $asString = false): mixed;

    public function forPDO(?array &$bounded = null): string;

    public function getMergedBounded(): array;

    /**
     * getSubQuery
     *
     * @param  string  $alias
     *
     * @return  Query|null
     */
    public function getSubQuery(string $alias): ?Query;

    /**
     * Method to get property Connection
     *
     * @return  Escaper
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEscaper(): ?Escaper;

    /**
     * Method to set property connection
     *
     * @param  Escaper|PDO|WeakReference|mixed  $escaper
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setEscaper(mixed $escaper): static;

    /**
     * Method to get property Grammar
     *
     * @return  AbstractGrammar
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGrammar(): AbstractGrammar;

    /**
     * getExpression
     *
     * @return  Expression
     */
    public function getExpression(): Expression;

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
     * createSubQuery
     *
     * @return  static
     */
    public function createSubQuery(): static;

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param  string|array  $clauses  Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return  static  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear(...$clauses): static;

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
     *
     * @codeCoverageIgnore
     */
    public function setDefaultItemClass(?string $defaultItemClass): static;

    public function do(string $name, ...$args): mixed;

    /**
     * Pipe a callback and return the result.
     *
     * @param  callable  $callback
     * @param  array     $args
     *
     * @return  static
     */
    public function pipe(callable $callback, ...$args): static;

    /**
     * Tap a callback and return self.
     *
     * @param  callable  $callback
     * @param  mixed     ...$args
     *
     * @return  static
     */
    public function tap(callable $callback, ...$args): static;

    /**
     * Pipe True/False callback based on a boolean value.
     *
     * @param  mixed          $allow
     * @param  callable|null  $trueCallback
     * @param  callable|null  $falseCallback
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function pipeIf(
        mixed $allow,
        ?callable $trueCallback = null,
        ?callable $falseCallback = null,
        ...$args
    ): static;

    /**
     * Tap True/False callback based on a boolean value.
     *
     * @param  mixed          $allow
     * @param  callable|null  $trueCallback
     * @param  callable|null  $falseCallback
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function tapIf(
        mixed $allow,
        ?callable $trueCallback = null,
        ?callable $falseCallback = null,
        ...$args
    ): static;

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
        $key = null,
        &$value = null,
        $dataType = null,
        int $length = 0,
        $driverOptions = null
    ): static;

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
    public function bind($key = null, $value = null, $dataType = null): static;

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
    public function getBounded($key = null): ?array;

    /**
     * resetBounded
     *
     * @return  static
     *
     * @codeCoverageIgnore
     */
    public function resetBounded(): static;

    /**
     * unbind
     *
     * @param  string|array  $keys
     *
     * @return  static
     */
    public function unbind(mixed $keys): static;

    public function formatDateTime(\DateTimeInterface $dateTime): string;

    public function stripQuote(string $str): string;

    public function stripNameQuote(string $str): string;

    /**
     * getAllTables
     *
     * @return  AsClause[]
     */
    public function getAllTables(): array;

    /**
     * @param  string  $expr
     * @param  bool    $instant
     *
     * @return  Clause
     *
     * @since  3.5.21
     */
    public function jsonSelector(string $expr, bool $instant = false): Clause;

    /**
     * @param  string  $expr
     * @param  mixed   $value
     *
     * @return  $this
     *
     * @throws  \JsonException
     */
    public function whereJsonContains(string $expr, mixed $value): static;

    public function whereJsonNotContains(string $expr, mixed $value): static;

    public function havingJsonContains(string $expr, mixed $value): static;

    public function havingJsonNotContains(string $expr, mixed $value): static;

    public function selectJsonLength(string $expr, string $as = ''): static;

    public function whereJsonLength(array|string $expr, mixed ...$args): static;

    /**
     * @param  string|array|QueryCallback|ClauseInterface  $column   Column name, array where list or callback
     *                                                               function as sub query.
     * @param  mixed                                       ...$args
     *
     * @return  static
     */
    public function where(mixed $column, mixed ...$args): static;

    /**
     * whereRaw
     *
     * @param  string|Clause  $string
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function whereRaw(Clause|string $string, ...$args): static;

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function orWhere(\Closure|array $wheres): static;

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function andWhere(\Closure|array $wheres): static;

    /**
     * @param  string|array|QueryCallback|ClauseInterface  $column   Column name, array where list or callback
     *                                                               function as sub query.
     * @param  mixed                                       ...$args
     *
     * @return  static
     */
    public function having(mixed $column, mixed ...$args): static;

    /**
     * @param  string|Clause  $string
     * @param  array          ...$args
     *
     * @return  static
     */
    public function havingRaw(mixed $string, mixed ...$args): static;

    /**
     * @param  array|QueryCallback  $wheres
     *
     * @return  static
     */
    public function orHaving(\Closure|array $wheres): static;

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function whereExists(Query|callable $conditions): static;

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function whereNotExists(Query|callable $conditions): static;

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function havingExists(Query|callable $conditions): static;

    /**
     * @param  Query|QueryCallable  $conditions
     *
     * @return  $this
     */
    public function havingNotExists(Query|callable $conditions): static;

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
    public function iterateBatched(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator;

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
    public function iterateChunks(CursorPaginate|int $paginate, ?string $class = null, array $args = []): \Generator;

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
    public function iterateWhile(int $length, ?string $class = null, array $args = []): \Generator;

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
    public function iterateBatchWhile(int $length, ?string $class = null, array $args = []): \Generator;

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
    ): \Traversable;

    public function getPaginatedIterator(
        ?string $class = null,
        CursorPaginate|int $perPage = 500,
        array $args = []
    ): PaginateIterator;
}
