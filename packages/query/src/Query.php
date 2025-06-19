<?php

declare(strict_types=1);

namespace Windwalker\Query;

use BadMethodCallException;
use Closure;
use DateTimeInterface;
use Generator;
use IteratorAggregate;
use PDO;
use ReflectionClass;
use SqlFormatter;
use Stringable;
use WeakReference;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\ORM\ORM;
use Windwalker\Query\Bounded\BindableInterface;
use Windwalker\Query\Bounded\BindableTrait;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\BoundedSequence;
use Windwalker\Query\Clause\AlterClause;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\ClausePosition;
use Windwalker\Query\Clause\ExprClause;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Clause\QuoteNameClause;
use Windwalker\Query\Clause\ValueClause;
use Windwalker\Query\Concern\JsonConcernTrait;
use Windwalker\Query\Concern\QueryConcernTrait;
use Windwalker\Query\Concern\ReflectConcernTrait;
use Windwalker\Query\Concern\WhereConcernTrait;
use Windwalker\Query\Exception\NoResultException;
use Windwalker\Query\Expression\Expression;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Wrapper\FormatRawWrapper;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Classes\ChainingTrait;
use Windwalker\Utilities\Classes\MarcoableTrait;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\WrapperInterface;

use function Windwalker\raw;
use function Windwalker\value;

/**
 * The Query class.
 *
 * @method string|null getType()
 * @method Clause|null getSelect()
 * @method Clause|null getDelete()
 * @method Clause|null getFrom()
 * @method Clause|null getJoin()
 * @method Clause|null getUnion()
 * @method Clause|null getWhere()
 * @method Clause|null getHaving()
 * @method Clause|null getOrder()
 * @method Clause|null getGroup()
 * @method int|null getLimit()
 * @method int|null getOffset()
 * @method Clause|null getInsert()
 * @method Clause|null getUpdate()
 * @method Clause|null getColumns()
 * @method Clause|null getValues()
 * @method Clause|null getSet()
 * @method Query[]     getSubQueries()
 * @method string|null getAlias()
 * @method Clause|null getPrefix()
 * @method Clause|null getSuffix()
 * @method string|null getSql()
 * @method bool getIncrementField()
 * @method $this leftJoin($table, ?string $alias = null, ...$on)
 * @method $this rightJoin($table, ?string $alias = null, ...$on)
 * @method $this outerJoin($table, ?string $alias = null, ...$on)
 * @method $this innerJoin($table, ?string $alias = null, ...$on)
 * @method $this whereIn($column, iterable $values)
 * @method $this whereNotIn($column, iterable $values)
 * @method $this whereBetween($column, $start, $end)
 * @method $this whereNotBetween($column, $start, $end)
 * @method $this whereLike($column, string $search)
 * @method $this whereNotLike($column, string $search)
 * @method $this havingIn($column, iterable $values)
 * @method $this havingNotIn($column, iterable $values)
 * @method $this havingBetween($column, $start, $end)
 * @method $this havingNotBetween($column, $start, $end)
 * @method $this havingLike($column, string $search)
 * @method $this havingNotLike($column, string $search)
 * @method string|array qn($text)
 * @method string|array q($text)
 *
 * @see AbstractStatement
 * @method Collection|object|null get(?string $class = null, array $args = [])
 * @method Collection|Collection[]|object[] all(?string $class = null, array $args = [])
 * @method Collection loadColumn(int|string $offset = 0)
 * @method mixed result(bool $throwsIfNotFound = false)
 * @method int count()
 * @method StatementInterface execute(?array $params = null)
 */
class Query implements QueryInterface, BindableInterface, IteratorAggregate
{
    use MarcoableTrait;
    use ChainingTrait;
    use BindableTrait;
    use QueryConcernTrait;
    use ReflectConcernTrait;
    use JsonConcernTrait;
    use WhereConcernTrait;

    public const string TYPE_SELECT = 'select';

    public const string TYPE_INSERT = 'insert';

    public const string TYPE_UPDATE = 'update';

    public const string TYPE_DELETE = 'delete';

    public const string TYPE_CUSTOM = 'custom';

    public const ClausePosition PREPEND = ClausePosition::PREPEND;
    public const ClausePosition APPEND = ClausePosition::APPEND;

    protected ?string $type = self::TYPE_SELECT;

    protected ?Clause $select = null;

    protected ?Clause $delete = null;

    protected ?Clause $from = null;

    protected ?Clause $join = null;

    protected ?Clause $union = null;

    protected ?Clause $order = null;

    protected ?Clause $group = null;

    protected ?int $limit = null;

    protected ?int $offset = null;

    protected ?Clause $insert = null;

    protected ?Clause $update = null;

    protected ?Clause $columns = null;

    protected Query|Clause|null $values = null;

    protected ?Clause $set = null;

    protected ?Clause $prefix = null;

    protected ?Clause $suffix = null;

    protected bool $incrementField = false;

    protected ?array $subQueries = [];

    protected ?AbstractGrammar $grammar = null;

    protected ?Expression $expression = null;

    protected ?string $alias = null;

    protected ?string $sql = null;

    protected ?BoundedSequence $sequence = null;

    protected ?Escaper $escaper = null;

    protected ?string $defaultItemClass = null;

    protected ?int $paginate = null;

    /**
     * Query constructor.
     *
     * @param  mixed|PDO|Escaper|AbstractDriver  $escaper
     * @param  AbstractGrammar|string|null       $grammar
     */
    public function __construct(mixed $escaper = null, mixed $grammar = null)
    {
        $this->grammar = $grammar instanceof AbstractGrammar
            ? $grammar
            : AbstractGrammar::create($grammar);

        $this->setEscaper($escaper);
    }

    public function getName(): string
    {
        return $this->getGrammar()::getName();
    }

    /**
     * select
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function select(...$columns): static
    {
        foreach (array_values(Arr::flatten($columns)) as $column) {
            $this->selectAs($column);
        }

        return $this;
    }

    /**
     * selectAs
     *
     * @param  mixed        $column
     * @param  string|null  $alias
     * @param  bool         $isColumn
     *
     * @return static
     */
    public function selectAs(mixed $column, ?string $alias = null, bool $isColumn = true): static
    {
        $this->selectRaw($this->as($column, $alias, $isColumn));

        return $this;
    }

    /**
     * selectRaw
     *
     * @param  string|array  $column
     * @param  mixed         ...$args
     *
     * @return  static
     */
    public function selectRaw(mixed $column, ...$args): static
    {
        if (is_array($column)) {
            foreach ($column as $col) {
                if (is_array($col)) {
                    $this->selectRaw(...$col);
                } else {
                    $this->selectRaw($col);
                }
            }

            return $this;
        }

        if (!$this->select) {
            $this->type = static::TYPE_SELECT;
            $this->select = $this->clause('SELECT', [], ', ');
        }

        if (is_string($column) && $args !== []) {
            $column = $this->format($column, ...$args);
        }

        $this->findAndInjectSubQueries($column);
        $this->select->append($column);

        return $this;
    }

    /**
     * from
     *
     * @param  string|array|Query  $tables
     * @param  string|null         $alias
     *
     * @return  static
     */
    public function from(mixed $tables, ?string $alias = null): static
    {
        $this->from ??= $this->clause('FROM', [], ', ');

        // if (!is_array($tables) && $alias !== null) {
        //     $tables = [$alias => $tables];
        // }

        if (is_array($tables)) {
            foreach ($tables as $table) {
                ArgumentsAssert::assert(
                    is_array($table),
                    '{caller} if use array as argument 1, every element should be a sub-array, '
                    . ' example: [\'foo\', \'f\'], got: %s.',
                    $table
                );

                $this->from(...$table);
            }

            return $this;
        }

        $this->from->append($this->as($tables, $alias));

        return $this;
    }

    /**
     * join
     *
     * @param  string                        $type
     * @param  string|Query|ClauseInterface  $table
     * @param  string|null                   $alias
     * @param  array                         $on
     *
     * @return  static
     */
    public function join(string $type, mixed $table, ?string $alias = null, ...$on): static
    {
        if (!$this->join) {
            $this->join = $this->clause('', [], ' ');
        }

        $tbl = $this->as($table, $alias);
        $joinType = strtoupper($type) . ' JOIN';

        $join = new JoinClause($this, $joinType, $tbl);

        if (count($on) === 1 && $on[0] instanceof Closure) {
            // ArgumentsAssert::assert(
            //     $on[0] instanceof \Closure,
            //     '%s if only has 1 on condition, it must be Closure, %s given.',
            //     $on[0]
            // );

            $on[0]($join);
        } elseif (count($on) <= 3) {
            $join->on(...$on);
        } else {
            ArgumentsAssert::assert(
                count($on) % 3 === 0,
                '{caller} if on is not callback, it must be 3 times as many, currently is %s.',
                count($on)
            );

            foreach (array_chunk($on, 3) as $cond) {
                $join->on(...$cond);
            }
        }

        $this->join->append($join);

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
        $clause = new AsClause($this, null, null, $isColumn);

        if ($value instanceof RawWrapper) {
            $clause->value($value);
        } elseif ($value instanceof ExprClause) {
            foreach ($value->getElements() as $v) {
                $this->handleAsValue($v, $alias);
            }

            $clause->value($value);
        } else {
            [$value, $alias] = $this->handleAsValue($value, $alias);

            $clause->value($value);
        }

        $clause->alias($alias);

        return $clause;
    }

    /**
     * handleAsValue
     *
     * @param  mixed  $value
     * @param  mixed  $alias
     *
     * @return  array
     */
    protected function handleAsValue(mixed $value, mixed $alias): array
    {
        if ($value instanceof Closure) {
            $q = $value($q = $this->createSubQuery()) ?? $q;
            $value = $q;
        }

        if ($value instanceof self) {
            $alias = $alias ?? $value->getAlias();

            $this->injectSubQuery($value, $alias);
        }

        if (is_string($value) && str_contains($value, '->')) {
            // For select
            if (stripos($value, ' as ') !== false) {
                [$value, $alias] = preg_split('/ as /i', $value);
            }

            $value = $this->jsonSelector($value);
        }

        return [$value, $alias];
    }

    protected function toRawSqlString(): string
    {
        $type = $this->type;

        if (!$type && $this->getFrom()) {
            $type = static::TYPE_SELECT;
        }

        if (!$type) {
            return '';
        }

        return $this->getGrammar()->compile((string) $type, $this);
    }

    private function prependPrimaryAlias(string $column): string
    {
        if (str_contains($column, '.') || !$this->getJoin()) {
            return $column;
        }

        if ($from = $this->getFrom()) {
            /** @var AsClause $as */
            $as = $from->getElements()[0];
            $alias = $as->getAlias();

            if ($alias) {
                return $alias . '.' . $column;
            }
        }

        return $column;
    }

    /**
     * Add a query to UNION with the current query.
     * Multiple unions each require separate statements and create an array of unions.
     *
     * @param  mixed   $query  The Query object or string to union.
     * @param  string  $type   The union type, can be `DISTINCT` or `ALL`, default is empty.
     *
     * @return  static    The Query object on success or boolean false on failure.
     *
     * @since   2.0
     */
    public function union(mixed $query, string $type = ''): static
    {
        $this->type = static::TYPE_SELECT;

        if (is_array($query)) {
            foreach ($query as $q) {
                $this->union($q, $type);
            }

            return $this;
        }

        // Clear any ORDER BY clause in UNION query
        // See http://dev.mysql.com/doc/refman/5.0/en/union.html
        // if (null !== $this->order) {
        //     $this->clear(['order', 'group']);
        // }

        // Create the Clause if it does not exist
        if (null === $this->union) {
            $prefix = 'UNION';

            if ($type !== '') {
                $prefix .= ' ' . $type;
            }

            $this->union = $this->clause($prefix . ' ()', [], ') ' . $prefix . ' (');
        }

        if ($query instanceof self) {
            $this->injectSubQuery($query, false);
        }

        $this->union->append($query);

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
     * @return  static   The Query object on success or boolean false on failure.
     *
     * @since   2.0
     */
    public function unionDistinct(mixed $query): static
    {
        // Apply the distinct flag to the union.
        return $this->union($query, 'DISTINCT');
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
        // Apply the distinct flag to the union.
        return $this->union($query, 'ALL');
    }

    private function val(mixed $value): ValueClause|QuoteNameClause
    {
        if ($value instanceof QuoteNameClause) {
            return $value->setQuery($this);
        }

        return new ValueClause($value);
    }

    /**
     * @param  array|string|ClauseInterface  $column
     * @param  string|null                   $dir
     * @param  ClausePosition                $pos
     *
     * @return  static
     */
    public function order(mixed $column, ?string $dir = null, ClausePosition $pos = ClausePosition::APPEND): static
    {
        if (is_array($column)) {
            foreach ($column as $col) {
                if (!is_array($col)) {
                    $col = [$col];
                }

                $this->order(...$col);
            }

            return $this;
        }

        $order = [$this->resolveColumn($column)];

        if ($dir !== null) {
            ArgumentsAssert::assert(
                in_array($dir = strtoupper($dir), ['ASC', 'DESC'], true),
                '{caller} argument 2 should be one of ASC/DESC, %s given',
                $dir
            );

            $order[] = $dir;
        }

        $this->orderRaw($this->clause('', $order), $pos);

        return $this;
    }

    public function orderRaw(string|Clause $order, mixed ...$args): static
    {
        if (!$this->order) {
            $this->order = $this->clause('ORDER BY', [], ', ');
        }

        $method = static::getClausePosition($args);

        if (is_string($order)) {
            $order = $this->format($order, ...$args);
        }

        $this->findAndInjectSubQueries($order);

        $this->order->$method($order);

        return $this;
    }

    /**
     * group
     *
     * @param  string|array  ...$columns
     *
     * @return  static
     */
    public function group(...$columns): static
    {
        if (!$this->group) {
            $this->group = $this->clause('GROUP BY', [], ', ');
        }

        $method = static::getClausePosition($columns);

        $this->group->$method(
            $this->qnMultiple(
                array_values(Arr::flatten($columns))
            )
        );

        return $this;
    }

    private static function getClausePosition(array &$args): string
    {
        if ($args === []) {
            return 'append';
        }

        $last = $args[array_key_last($args)];

        if ($last instanceof ClausePosition) {
            array_pop($args);

            return match ($last) {
                ClausePosition::PREPEND => 'prepend',
                ClausePosition::APPEND => 'append',
            };
        }

        return 'append';
    }

    /**
     * @param  int|null  $limit
     *
     * @return  static
     */
    public function limit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param  int|null  $offset
     *
     * @return  static
     */
    public function offset(?int $offset): static
    {
        $this->offset = $offset;

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
        $this->type = static::TYPE_INSERT;
        $this->insert = $this->clause('INSERT INTO', $this->as($table, false));
        $this->incrementField = $incrementField;

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
        $this->type = static::TYPE_UPDATE;
        $this->update = $this->clause('UPDATE', $this->as($table, $alias));

        return $this;
    }

    public function delete(string $table, ?string $alias = null): static
    {
        $this->type = static::TYPE_DELETE;

        if (!$this->delete) {
            $this->delete = $this->clause('DELETE');
        }

        $this->from($table, $alias);

        return $this;
    }

    /**
     * columns
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function columns(...$columns): static
    {
        if (!$this->columns) {
            $this->columns = $this->clause('()', [], ', ');
        }

        $method = static::getClausePosition($columns);

        $this->columns->$method(
            $this->qnMultiple(
                array_values(Arr::flatten($columns))
            )
        );

        return $this;
    }

    /**
     * values
     *
     * @param  mixed  ...$values
     *
     * @return  static
     */
    public function values(...$values): static
    {
        if ($values === []) {
            return $this;
        }

        $method = static::getClausePosition($values);

        foreach ($values as $value) {
            if ($value instanceof self) {
                if (!$this->values) {
                    $this->values = $this->createSubQuery();
                    $this->injectSubQuery($this->values);
                }

                ArgumentsAssert::assert(
                    $this->values instanceof self,
                    'You must set sub query as values to {caller} since current mode is ' .
                    'INSERT ... SELECT ..., %s given',
                    $value
                );

                $this->values->union($value);
            } else {
                if (!$this->values) {
                    $this->values = $this->clause('VALUES ', [], ', ');
                }

                ArgumentsAssert::assert(
                    $this->values instanceof Clause,
                    'You must set array as values to {caller} since current mode is VALUES (...), %s given',
                    $value
                );

                ArgumentsAssert::assert(
                    is_iterable($value),
                    'Please set every value as array or iterator, ' .
                    'example: value(array, array, array), {value} given.',
                    $value
                );

                $clause = $this->clause('()', [], ', ');

                foreach ($value as $val) {
                    $clause->append($this->castWriteValue($val));
                }

                $this->values->$method($clause);
            }
        }

        return $this;
    }

    public function set(iterable|string $column, mixed $value = null): static
    {
        if (is_iterable($column)) {
            foreach ($column as $col => $val) {
                $this->set($col, $val);
            }

            return $this;
        }

        if ($value instanceof Closure) {
            $value($value = $this->createSubQuery());
        }

        $this->setRaw(
            $this->clause(
                '',
                [$this->quoteName($column), '=', $this->castWriteValue($value)]
            )
        );

        return $this;
    }

    public function setRaw(Clause|string $value, ...$args): static
    {
        if (!$this->set) {
            $this->set = $this->clause('SET', [], ', ');
        }

        if (is_string($value) && $args !== []) {
            $value = $this->format($value, ...$args);
        }

        $this->set->append($value);

        return $this;
    }

    private function castWriteValue(mixed $value): ValueClause
    {
        $origin = $value;

        if ($value === null) {
            $value = $this->val(raw('NULL'));
        } elseif ($value instanceof self) {
            // Process Aub query object
            $value = $this->val($value);
            $this->injectSubQuery($origin);
        } elseif ($value instanceof RawWrapper) {
            // Process Raw
            $value = $this->val($value);
        } elseif (is_bool($value)) {
            $value = $this->val(raw($value ? 'true' : 'false'));
        } else {
            // ArgumentsAssert::assert(
            //     !is_array($value) && !is_object($value),
            //     'Write values should be scalar or NULL, %2$s given.',
            //     $value
            // );

            // Process simple value compare
            $this->bind(null, $value = $this->val($value));
        }

        return $value;
    }

    /**
     * injectSubQuery
     *
     * @param  Query             $query
     * @param  string|bool|null  $alias
     *
     * @return  void
     */
    private function injectSubQuery(Query $query, mixed $alias = null): void
    {
        $alias = $alias ?: $query->getAlias() ?: spl_object_hash($query);

        $this->subQueries[$alias] = $query;
    }

    private function findAndInjectSubQueries(mixed $clause): void
    {
        if ($clause instanceof self) {
            $clause = [$clause];
        }

        if ($clause instanceof Clause) {
            $clause = $clause->getElements();
        }

        if (is_iterable($clause)) {
            foreach ($clause as $element) {
                if ($element instanceof self) {
                    $this->injectSubQuery($element);
                }
            }
        }
    }

    /**
     * @param  string        $name
     * @param  array|string  $elements
     * @param  string        $glue
     *
     * @return  Clause
     */
    public function clause(string $name, mixed $elements = [], string $glue = ' '): Clause
    {
        return clause($name, $elements, $glue);
    }

    public function expr(string $name, ...$elements): ExprClause
    {
        return expr($name, ...$elements);
    }

    public function alter(string $target, string $targetName): AlterClause
    {
        return (new AlterClause($this))->target($target, $targetName);
    }

    /**
     * escape
     *
     * @param  string|iterable|WrapperInterface  $value
     *
     * @return  string|array
     */
    public function escape(mixed $value): array|string
    {
        $value = value($value);

        if (is_iterable($value)) {
            foreach ($value as &$v) {
                $v = $this->escape($v);
            }

            return $value;
        }

        return $this->getEscaper()->escape((string) $value);
    }

    /**
     * quote
     *
     * @param  mixed|WrapperInterface  $value
     *
     * @return string
     */
    public function quote(mixed $value): mixed
    {
        if ($value instanceof RawWrapper) {
            return value($value);
        }

        $value = value($value);

        if (is_iterable($value)) {
            foreach ($value as &$v) {
                $v = $this->quote($v);
            }

            return $value;
        }

        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $this->getEscaper()?->quote((string) $value);
    }

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
    public function valueize(mixed $value, bool $instant): mixed
    {
        if ($instant) {
            return $this->quote($value);
        }

        $this->bind(null, $vc = val($value));

        return $vc;
    }

    /**
     * quoteName
     *
     * @param  string|Stringable  $name
     * @param  int                $flags
     *
     * @return mixed
     */
    public function quoteName(string|Stringable $name, int $flags = 0): string|Clause
    {
        if ($name instanceof RawWrapper) {
            return $name();
        }

        return $this->getGrammar()::quoteName($name, (bool) ($flags & QN_IGNORE_DOTS));
    }

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
    public function resolveColumn(string|Stringable $name, int $flags = 0): string
    {
        if ($name instanceof RawWrapper) {
            return $name();
        }

        $name = (string) $name;

        if (str_contains($name, '->')) {
            return (string) $this->jsonSelector($name, true);
        }

        return $this->quoteName($name, $flags);
    }

    public function qnMultiple(iterable|Clause|WrapperInterface $names, int $flags = 0): mixed
    {
        if ($names instanceof RawWrapper) {
            return $names();
        }

        if ($names instanceof Clause) {
            return $names->mapElements(fn($item) => $this->quoteName($item, $flags));
        }

        $quoted = [];

        foreach ($names as $k => $name) {
            $quoted[$k] = $this->quoteName($name, $flags);
        }

        return $quoted;
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
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param  string|array  $prefix
     * @param  mixed         ...$args
     *
     * @return  static
     */
    public function prefix(array|string $prefix, ...$args): static
    {
        if (is_array($prefix)) {
            foreach ($prefix as $values) {
                if (is_string($values)) {
                    $this->prefix($values);
                } else {
                    $this->prefix(...$values);
                }
            }

            return $this;
        }

        if (null === $this->prefix) {
            $this->prefix = $this->clause('', []);
        }

        if (is_string($prefix) && $args !== []) {
            $prefix = $this->format($prefix, ...$args);
        }

        $this->prefix->append($prefix);

        return $this;
    }

    /**
     * suffix
     *
     * @param  string|array  $suffix
     * @param  mixed         ...$args
     *
     * @return  static
     */
    public function suffix(array|string $suffix, ...$args): static
    {
        if (is_array($suffix)) {
            foreach ($suffix as $values) {
                if (is_string($values)) {
                    $this->suffix($values);
                } else {
                    $this->suffix(...$values);
                }
            }

            return $this;
        }

        if (null === $this->suffix) {
            $this->suffix = $this->clause('', []);
        }

        if (is_string($suffix) && $args !== []) {
            $suffix = $this->format($suffix, ...$args);
        }

        $this->suffix->append($suffix);

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
        $suffix = 'FOR ' . $for;

        if ($do) {
            $suffix .= ' ' . $do;
        }

        return $this->suffix($suffix);
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
        return $this->rowLock('UPDATE', $do);
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
        return $this->rowLock('SHARE', $do);
    }

    /**
     * sql
     *
     * @param  string  $sql
     * @param  mixed   ...$args
     *
     * @return  static
     */
    public function sql(string $sql, ...$args): static
    {
        $this->type = static::TYPE_CUSTOM;

        if ($args !== []) {
            $sql = $this->format($sql, ...$args);
        }

        $this->sql = $sql;

        return $this;
    }

    public function nullDate(): string
    {
        return $this->getGrammar()::nullDate();
    }

    public function getDateFormat(): string
    {
        return $this->getGrammar()::dateFormat();
    }

    /**
     * castValue
     *
     * @param  mixed  $value
     *
     * @return  string
     */
    public function castValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $this->formatDateTime($value);
        }

        return $value;
    }

    public function raw(string $string, ...$args): RawWrapper
    {
        if ($args === []) {
            return raw($string);
        }

        return new FormatRawWrapper($this, $string, $args);
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
    public function format(string $format, ...$args): string
    {
        array_unshift($args, null);

        $expression = $this->getExpression();

        $i = 1;
        $replace = function ($sign, $replacement) use ($expression) {
            return match ($sign) {
                'a' => TypeCast::mustNumeric($replacement),
                'b' => TypeCast::mustBoolean($replacement) ? 'true' : 'false',
                'e' => $this->escape($replacement),
                'n' => $this->resolveColumn($replacement, QN_JSON_INSTANT),
                'q' => $this->quote($replacement),
                'r' => $replacement,
                'y' => $expression->year($this->quote($replacement)),
                'Y' => $expression->year($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                'm' => $expression->month($this->quote($replacement)),
                'M' => $expression->month($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                'd' => $expression->day($this->quote($replacement)),
                'D' => $expression->day($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                'h' => $expression->hour($this->quote($replacement)),
                'H' => $expression->hour($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                'i' => $expression->minute($this->quote($replacement)),
                'I' => $expression->minute($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                's' => $expression->second($this->quote($replacement)),
                'S' => $expression->second($this->resolveColumn($replacement, QN_JSON_INSTANT)),
                default => '',
            };
        };

        $func = function ($match) use ($replace, $args, &$i, $expression) {
            if (isset($match[6]) && $match[6] === '%') {
                return '%';
            }

            // No argument required, do not increment the argument index.
            switch ($match[5]) {
                case 't':
                    return $expression->currentTimestamp();
                    break;

                case 'z':
                    return $this->nullDate();
                    break;

                case 'Z':
                    return $this->quote($this->nullDate());
                    break;
            }

            // Increment the argument index only if argument specifier not provided.
            $index = is_numeric($match[4]) ? (int) $match[4] : $i++;

            if (!$index || !isset($args[$index])) {
                $replacement = '';
            } else {
                $replacement = $args[$index];
            }

            // Handle array values
            if (is_array($replacement)) {
                $result = [];

                foreach ($replacement as $v) {
                    $result[] = $replace($match[5], $v);
                }

                return implode(', ', $result);
            }

            return $replace($match[5], $replacement);
        };

        /**
         * Regexp to find an replace all tokens.
         * Matched fields:
         * 0: Full token
         * 1: Everything following '%'
         * 2: Everything following '%' unless '%'
         * 3: Argument specifier and '$'
         * 4: Argument specifier
         * 5: Type specifier
         * 6: '%' if full token is '%%'
         */
        return preg_replace_callback('#%(((([\d]+)\$)?([abeEnqQryYmMdDhHiIsStzZ]))|(%))#', $func, $format);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->render();
    }

    public function render(bool $emulatePrepared = false, ?array &$bounded = []): string
    {
        $bounded = $bounded ?? [];

        $topLevel = !$this->sequence;

        // Only top level query rendering should create sequence and get merged bounded
        if ($topLevel) {
            $bounded = $this->mergeBounded();
        }

        $sql = $this->toRawSqlString();
        $sql = $this->handleVariadicParams($sql);

        if ($topLevel) {
            // Convert the ValueClause to scalars,
            // ValueClause objects will set to linked when they were converted to string,
            // we only keep linked ValueClause and convert them to pure value.
            // so that we can keep the correct bounded values numbers if you cleared any Query clauses.
            $bounded = array_filter(
                $bounded,
                static fn(mixed $param) => !$param['value'] instanceof ValueClause || $param['value']->isLinked()
            );
            $bounded = array_map(
                function (array $param) {
                    if ($param['value'] instanceof ValueClause) {
                        $param['value'] = $this->castValue($param['value']->getValue());
                    }

                    return $param;
                },
                $bounded
            );
        }

        if ($emulatePrepared) {
            $sql = BoundedHelper::emulatePrepared($this->getEscaper(), $sql, $bounded);
        }

        // Clear sequence so that next time rendering should re-create new one
        $this->sequence = null;

        return $sql;
    }

    /**
     * debug
     *
     * @param  bool  $pre
     * @param  bool  $format
     * @param  bool  $asString
     *
     * @return mixed|static
     */
    public function debug(bool $pre = false, bool $format = true, bool $asString = false): mixed
    {
        $sql = $this->render(true);

        if ($format && class_exists(SqlFormatter::class)) {
            $sql = SqlFormatter::format($sql, false);
        }

        if ($pre) {
            $sql = '<pre class="c-windwalker-db-query">' . $sql . '</pre>';
        }

        if ($asString) {
            return $sql;
        }

        echo $sql;

        return $this;
    }

    public function forPDO(?array &$bounded = []): string
    {
        $bounded = $bounded ?: [];

        // Only top level query rendering should create sequence and get merged bounded
        if (!$this->sequence) {
            $bounded = $this->mergeBounded();
        }

        $sql = $this->toRawSqlString();

        [$sql, $bounded] = BoundedHelper::forPDO($sql, $bounded);

        return $sql;
    }

    public function getMergedBounded(): array
    {
        $bounded = $this->mergeBounded();

        $this->sequence = null;

        return $bounded;
    }

    private function mergeBounded(?BoundedSequence $sequence = null): array
    {
        $this->sequence = $sequence = $sequence ?: new BoundedSequence('wqp__');

        $all = [];
        $bounded = [];

        $params = $this->getBounded();

        foreach ($params as $key => $param) {
            if ($param['value'] instanceof ValueClause) {
                $param['value']->setPlaceholder($sequence->get());
                $key = $param['value']->getPlaceholder();

                // Pre-set every ValueClause to unlinked.
                // They will be auto set to linked later if they are converting to string.
                // At the top level Query object, will ignore all unlinked ValueClause,
                // so that can keep the correct bounded values numbers if you cleared any Query clauses.
                $param['value']->setLinked(false);
            }
            $bounded[$key] = $param;
        }

        $all[] = $bounded;

        foreach ($this->getSubQueries() as $subQuery) {
            $all[] = $subQuery->mergeBounded($sequence);
        }

        return array_merge(...$all);
    }

    /**
     * getSubQuery
     *
     * @param  string  $alias
     *
     * @return  Query|null
     */
    public function getSubQuery(string $alias): ?Query
    {
        return $this->subQueries[$alias] ?? null;
    }

    /**
     * Method to get property Connection
     *
     * @return  Escaper
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEscaper(): ?Escaper
    {
        return $this->escaper;
    }

    /**
     * Method to set property connection
     *
     * @param  Escaper|PDO|WeakReference|mixed  $escaper
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setEscaper(mixed $escaper): static
    {
        if ($escaper === null) {
            $escaper = DefaultConnection::getEscaper();
        }

        $this->escaper = $escaper instanceof Escaper ? $escaper : new Escaper($escaper, $this);

        $this->grammar->setEscaper($this->escaper);

        return $this;
    }

    /**
     * Method to get property Grammar
     *
     * @return  AbstractGrammar
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGrammar(): AbstractGrammar
    {
        return $this->grammar;
    }

    /**
     * getExpression
     *
     * @return  Expression
     */
    public function getExpression(): Expression
    {
        if ($this->expression) {
            return $this->expression;
        }

        $class = sprintf(__NAMESPACE__ . '\\Expression\\' . $this->grammar::getName() . 'Expression');

        if (!class_exists($class)) {
            $class = Expression::class;
        }

        return $this->expression = new $class($this);
    }

    /**
     * Method to provide deep copy support to nested objects and arrays
     * when cloning.
     *
     * @return  void
     */
    public function __clone()
    {
        foreach (get_object_vars($this) as $k => $v) {
            if (is_object($v)) {
                $this->{$k} = clone $v;
            }
        }
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
        return $this->get($class, $args)
            ?? throw new NoResultException(
                TypeCast::tryString($this->getFrom()),
                $this
            );
    }

    public function mustGetResult(): mixed
    {
        try {
            return $this->result(true);
        } catch (StatementException $e) {
            if ($e->getCode() === 404) {
                throw new NoResultException(
                    TypeCast::tryString($this->getFrom()),
                    $this,
                    $e
                );
            }

            throw $e;
        }
    }

    /**
     * createSubQuery
     *
     * @return  static
     */
    public function createSubQuery(): static
    {
        return new static($this->escaper, $this->grammar);
    }

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param  string|array  $clauses  Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return static  Returns this object to allow chaining.
     *
     * @since   2.0
     */
    public function clear(...$clauses): static
    {
        $clauses = Arr::collapse($clauses);

        if (count($clauses) === 1) {
            $clauses = array_pop($clauses);
        }

        $handlers = [
            'select' => ['type'],
            'delete' => ['type'],
            'update' => ['type'],
            'insert' => ['type', 'incrementField'],
            'sql' => ['type'],
            'from' => [],
            'join' => [],
            'set' => [],
            'where' => [],
            'group' => [],
            'having' => [],
            'order' => [],
            'columns' => [],
            'values' => [],
            'limit' => [],
            'offset' => [],
            'prefix' => [],
            'suffix' => [],
            'union' => [],
            'alias' => [],
            'subQueries' => [],
            'bounded' => [],
            'defaultItemClass' => null,
            'paginate' => null,
        ];

        if ($clauses === []) {
            $clauses = array_keys($handlers);
        }

        if (is_array($clauses)) {
            foreach ($clauses as $clause) {
                $this->clear($clause);
            }

            return $this;
        }

        $props = (new ReflectionClass($this))->getDefaultProperties();
        $this->$clauses = $props[$clauses] ?? null;

        foreach ($handlers[$clauses] ?? [] as $field) {
            $this->$field = $props[$field] ?? null;
        }

        return $this;
    }

    public function __call(string $name, array $args): mixed
    {
        // Simple Alias
        $aliases = [
            'qn' => 'quoteName',
            'q' => 'quote',
        ];

        if (isset($aliases[$name])) {
            return $this->{$aliases[$name]}(...$args);
        }

        // Get Fields
        $field = lcfirst((string) substr($name, 3));

        if (property_exists($this, $field)) {
            return $this->$field;
        }

        // Where/Having
        if (str_starts_with(strtolower($name), 'where')) {
            $operator = substr($name, 5);

            return $this->whereVariant('where', $operator, $args);
        }

        if (str_starts_with(strtolower($name), 'having')) {
            $operator = substr($name, 6);

            return $this->whereVariant('having', $operator, $args);
        }

        // Join
        $aliases = [
            'leftJoin' => 'LEFT',
            'rightJoin' => 'RIGHT',
            'innerJoin' => 'INNER',
            'outerJoin' => 'OUTER',
            'crossJoin' => 'CROSS',
        ];

        if (isset($aliases[$name])) {
            return $this->join($aliases[$name], ...$args);
        }

        // Load
        $methods = [
            'get',
            'all',
            'result',
            'loadcolumn',
            'execute',
        ];

        if (in_array(strtolower($name), $methods)) {
            return $this->prepareStatement()->$name(...$args);
        }

        if (strtolower($name) === 'count') {
            return $this->getDbConnection()->countWith($this);
        }

        throw new BadMethodCallException(
            sprintf('Call to undefined method of: %s::%s()', static::class, $name)
        );
    }

    /**
     * @param  string|null  $class
     * @param  array        $args
     * @param  int|null     $paginate
     *
     * @return  Generator
     */
    public function getIterator(?string $class = null, array $args = [], ?int $paginate = null): Generator
    {
        $paginate ??= $this->getPaginate();

        if ($paginate !== null) {
            return $this->getPaginatedIterator($class, $paginate, $args);
        }

        return $this->prepareStatement()->getIterator($class, $args);
    }

    public function getPaginatedIterator(?string $class = null, int $perPage = 500, array $args = []): Generator
    {
        $offset = $this->getOffset() ?? 0;
        $leave = $this->getLimit();

        do {
            $i = 0;

            if ($leave !== null) {
                $perPage = min($leave, $perPage);
                $leave -= $perPage;
            }

            $query = clone $this;
            $query->offset($offset)->limit($perPage);

            foreach ($query->getIterator($class, $args) as $item) {
                yield $item;

                $i++;
            }

            $offset += $perPage;

            if ($leave === 0) {
                break;
            }
        } while ($i > 0);
    }

    public function prepareStatement(): StatementInterface
    {
        $statement = $this->getDbConnection()->prepare($this);

        if ($this->defaultItemClass) {
            $statement->setDefaultItemClass($this->defaultItemClass);
        }

        return $statement;
    }

    private function getDbConnection(): DatabaseAdapter
    {
        $db = $this->getEscaper()->getConnection();

        if (!($db instanceof AbstractDriver || $db instanceof DatabaseAdapter || $db instanceof ORM)) {
            throw new BadMethodCallException(
                sprintf(
                    'Directly fetch from DB must use %s or %s as Escaper::$connection.',
                    AbstractDriver::class,
                    DatabaseAdapter::class
                )
            );
        }

        if ($db instanceof ORM) {
            $db = $db->getDb();
        }

        return $db;
    }

    /**
     * @return string|null
     */
    public function getDefaultItemClass(): ?string
    {
        return $this->defaultItemClass;
    }

    /**
     * @param  string|null  $defaultItemClass
     *
     * @return  static  Return self to support chaining.
     */
    public function setDefaultItemClass(?string $defaultItemClass): static
    {
        $this->defaultItemClass = $defaultItemClass;

        return $this;
    }

    public function getPaginate(): ?int
    {
        return $this->paginate;
    }

    public function paginate(?int $paginate): static
    {
        $this->paginate = $paginate;

        return $this;
    }
}
