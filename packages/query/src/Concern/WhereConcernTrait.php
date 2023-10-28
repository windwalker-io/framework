<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Closure;
use InvalidArgumentException;
use MyCLabs\Enum\Enum;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\QuoteNameClause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\raw;

/**
 * Trait WhereConcernTrait
 */
trait WhereConcernTrait
{
    protected ?Clause $where = null;

    protected ?Clause $having = null;

    /**
     * where
     *
     * @param  string|array|Closure|ClauseInterface  $column   Column name, array where list or callback
     *                                                         function as sub query.
     * @param  mixed                                 ...$args
     *
     * @return  static
     */
    public function where(mixed $column, mixed ...$args): static
    {
        if ($column instanceof Closure) {
            $this->handleNestedWheres($column, (string) ($args[0] ?? 'AND'));

            return $this;
        }

        if (is_array($column)) {
            return static::convertAllToWheres($this, $column);
        }

        if (is_string($column)) {
            $column = $this->prependPrimaryAlias($column);
        }

        // Handle single expression or sub query condition
        if ($args === [] && ($column instanceof Clause || $column instanceof self)) {
            $this->as($column, false);
            $this->whereRaw($column);
            return $this;
        }

        $column = $this->as($column, false);

        [$operator, $value] = $this->handleOperatorAndValue(
            $args[0] ?? null,
            $args[1] ?? null,
            count($args) === 1
        );

        $this->whereRaw(
            $this->clause(
                '',
                [$column, $operator, $value]
            )
        );

        return $this;
    }

    /**
     * Handle value and operator.
     *
     * This method will wrap value as a ValueObject and inject into bounded params.
     * By default, where clause uses `?` as placeholder and bind variables to prepared statement.
     * But it is hard to handle sub queries' placeholder ordering.
     *
     * ValueObject will be injected to bounded temporaries so that we can change `?` to
     * a named param like: `:wqp__{ordering}` and re-calc the order when every time rendering Query object,
     * so we can make sure the variables won't be conflict.
     *
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  bool   $shortcut
     *
     * @return  array
     */
    private function handleOperatorAndValue(mixed $operator, mixed $value, bool $shortcut = false): array
    {
        if ($shortcut) {
            [$operator, $value] = ['=', $operator];
        }

        if ($operator === null) {
            throw new InvalidArgumentException('Where operator should not be NULL');
        }

        // Closure means to create a sub query as value.
        if ($value instanceof Closure) {
            $value($value = $this->createSubQuery());
        }

        // This should be deprecated after php8.1
        if ($value instanceof Enum) {
            $value = $value->getValue();
        }

        $this->findAndInjectSubQueries($value);

        // Keep origin value a duplicate that we will need it later.
        // The $value will make it s a ValueClause object and inject to bounded params,
        // so that we can use it to generate prepared param placeholders.
        // The $origin variable is to store origin value at Query object if needed.
        $origin = $value;

        if ($value === null) {
            // Process NULL
            if ($operator === '=') {
                $operator = 'IS';
            } elseif ($operator === '!=') {
                $operator = 'IS NOT';
            }

            $value = $this->val(raw('NULL'));
        } elseif (in_array(strtolower($operator), ['between', 'not between'], true)) {
            // Process BETWEEN
            ArgumentsAssert::assert(
                is_array($value) && COUNT($value) === 2,
                'Between should have at least and only 2 values'
            );

            $value = $this->clause('', [], ' AND ');

            foreach ($origin as $val) {
                // Append every value as ValueObject so that we can make placeholders as `IN(?, ?, ?...)`
                $value->append($vc = $this->val($val));

                $this->bind(null, $vc);
            }
        } elseif ($value instanceof self) {
            // Process Sub query object
            $value = $this->val($value);
        } elseif (is_iterable($value)) {
            $origin = TypeCast::toArray($origin);

            // Auto convert array value as IN() clause.
            if ($operator === '=') {
                $operator = 'IN';
            } elseif ($operator === '!=') {
                $operator = 'NOT IN';
            }

            $value = $this->clause('()', [], ', ');

            foreach ($origin as $val) {
                // Append every value as ValueObject so that we can make placeholders as `IN(?, ?, ?...)`
                $value->append($this->handleValueAndBind($val));
            }
        } else {
            $value = $this->handleValueAndBind($value);
        }

        return [strtoupper($operator), $value, $origin];
    }

    private function handleValueAndBind(mixed $value): mixed
    {
        if ($value instanceof RawWrapper || $value instanceof QuoteNameClause) {
            // Process Raw
            $value = $this->val($value);
        } else {
            // Process simple value compare
            $this->bind(null, $value = $this->val($value));
        }

        return $value;
    }

    private function handleNestedWheres(Closure $callback, string $glue, string $type = 'where'): void
    {
        if (!in_array(strtolower(trim($glue)), ['and', 'or'], true)) {
            throw new InvalidArgumentException('WHERE glue should only be `OR`, `AND`.');
        }

        $callback($query = $this->createSubQuery());

        /** @var Clause $where */
        $where = $query->$type;

        // If where clause not exists, means this callback has no where call, just return.
        if (!$where) {
            return;
        }

        $this->{$type . 'Raw'}(
            $where->setName('()')
                ->setGlue(' ' . strtoupper($glue) . ' ')
        );

        foreach ($query->getBounded() as $key => $param) {
            if (TypeCast::tryInteger($key, true) !== null) {
                $this->bounded[] = $param;
            } else {
                $this->bounded[$key] = $param;
            }
        }

        foreach ($query->getSubQueries() as $subQuery) {
            $this->injectSubQuery($subQuery);
        }
    }

    /**
     * whereRaw
     *
     * @param  string|Clause  $string
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function whereRaw(Clause|string $string, ...$args): static
    {
        if (!$this->where) {
            $this->where = $this->clause('WHERE', [], ' AND ');
        }

        if (is_string($string) && $args !== []) {
            $string = $this->format($string, ...$args);
        }

        $this->findAndInjectSubQueries($string);

        $this->where->append($string);

        return $this;
    }

    /**
     * orWhere
     *
     * @param  array|Closure  $wheres
     *
     * @return  static
     */
    public function orWhere(array|Closure $wheres): static
    {
        if (is_array($wheres)) {
            return $this->orWhere(
                static function (Query $query) use ($wheres) {
                    foreach ($wheres as $where) {
                        $query->where(...$where);
                    }
                }
            );
        }

        ArgumentsAssert::assert(
            $wheres instanceof Closure,
            '{caller} argument should be array or Closure, %s given.',
            $wheres
        );

        return $this->where($wheres, 'OR');
    }

    /**
     * orWhere
     *
     * @param  array|Closure  $wheres
     *
     * @return  static
     */
    public function andWhere(array|Closure $wheres): static
    {
        if (is_array($wheres)) {
            return $this->andWhere(
                static function (Query $query) use ($wheres) {
                    foreach ($wheres as $where) {
                        $query->where(...$where);
                    }
                }
            );
        }

        ArgumentsAssert::assert(
            $wheres instanceof Closure,
            '{caller} argument should be array or Closure, %s given.',
            $wheres
        );

        return $this->where($wheres, 'AND');
    }

    public function having(mixed $column, mixed ...$args): static
    {
        if ($column instanceof Closure) {
            $this->handleNestedWheres($column, (string) ($args[0] ?? 'AND'), 'having');

            return $this;
        }

        if (is_array($column)) {
            return static::convertAllToWheres($this, $column, 'having');
        }

        if (is_string($column)) {
            $column = $this->prependPrimaryAlias($column);
        }

        // Handle single expression or sub query condition
        if ($args === [] && ($column instanceof Clause || $column instanceof self)) {
            $this->as($column, false);
            $this->havingRaw($column);
            return $this;
        }

        $column = $this->as($column, false);

        [$operator, $value] = $this->handleOperatorAndValue(
            $args[0] ?? null,
            $args[1] ?? null,
            count($args) === 1
        );

        $this->havingRaw(
            $this->clause(
                '',
                [$column, $operator, $value]
            )
        );

        return $this;
    }

    /**
     * havingRaw
     *
     * @param  mixed  $string
     * @param  array  ...$args
     *
     * @return  static
     */
    public function havingRaw(mixed $string, mixed ...$args): static
    {
        if (!$this->having) {
            $this->having = $this->clause('HAVING', [], ' AND ');
        }

        if (is_string($string) && $args !== []) {
            $string = $this->format($string, ...$args);
        }

        $this->findAndInjectSubQueries($string);

        $this->having->append($string);

        return $this;
    }

    /**
     * orWhere
     *
     * @param  array|Closure  $wheres
     *
     * @return  static
     */
    public function orHaving(array|Closure $wheres): static
    {
        if (is_array($wheres)) {
            return $this->orHaving(
                static function (Query $query) use ($wheres) {
                    foreach ($wheres as $where) {
                        $query->having(...$where);
                    }
                }
            );
        }

        ArgumentsAssert::assert(
            $wheres instanceof Closure,
            '{caller} argument should be array or Closure, %s given.',
            $wheres
        );

        return $this->having($wheres, 'OR');
    }

    public function whereExists(Query|callable $conditions): static
    {
        return $this->handleWhereExists('where', $conditions, false);
    }

    public function whereNotExists(Query|callable $conditions): static
    {
        return $this->handleWhereExists('where', $conditions, true);
    }

    public function havingExists(Query|callable $conditions): static
    {
        return $this->handleWhereExists('having', $conditions, false);
    }

    public function havingNotExists(Query|callable $conditions): static
    {
        return $this->handleWhereExists('having', $conditions, true);
    }

    private function handleWhereExists(string $type, Query|callable $conditions, bool $not = false): static
    {
        if (is_callable($conditions)) {
            $subQuery = $this->createSubQuery();
            $subQuery = $conditions($subQuery) ?? $subQuery;
        } else {
            $subQuery = $conditions;
        }

        $exprName = $not ? 'NOT EXISTS()' : 'EXISTS()';

        return $this->{$type . 'Raw'}(
            $this->expr(
                $exprName,
                $subQuery
            )
        );
    }

    private function whereVariant(string $type, string $operator, array $args)
    {
        $maps = [
            'notin' => 'not in',
            'notbetween' => 'not between',
            'notlike' => 'not like',
        ];

        $operator = strtolower($operator);

        if (str_starts_with($operator, 'not')) {
            $operator = 'not ' . substr($operator, 3);
        }

        $operator = $maps[$operator] ?? $operator;

        $arg1 = array_shift($args);

        if (in_array($operator, ['between', 'not between'], true)) {
            ArgumentsAssert::assert(
                count($args) === 2,
                'BETWEEN or NOT BETWEEN needs 2 values'
            );

            return $this->$type($arg1, $operator, $args);
        }

        $arg2 = array_shift($args);

        return $this->$type($arg1, $operator, $arg2);
    }
}
