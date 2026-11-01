<?php

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Query\Clause\Clause;

/**
 * JSON related query methods.
 *
 * @psalm-type QueryCallback = \Closure(Query): mixed
 * @psalm-type QueryCallable = callable(Query): mixed
 */
interface JsonQueryInterface
{
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
}
