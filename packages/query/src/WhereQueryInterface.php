<?php

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ClauseInterface;

/**
 * Where / having related query methods.
 *
 * @psalm-type QueryCallback = \Closure(Query): mixed
 * @psalm-type QueryCallable = callable(Query): mixed
 */
interface WhereQueryInterface
{
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
    public function whereRaw(Clause|string $string, mixed ...$args): static;

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
}
