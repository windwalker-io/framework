<?php

declare(strict_types=1);

namespace Windwalker\Query\Contract;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

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
     *
     * @deprecated  Use {@see whereJsonContainsAll()} instead.
     *               In the next major release, whereJsonContainsAll() will be renamed back to whereJsonContains()
     *               and the new behavior will be adopted.
     */
    public function whereJsonContains(string $expr, mixed $value): static;

    /**
     * Arrays must have all elements contained, other values are treated as a single element.
     */
    public function whereJsonContainsAll(string $expr, mixed $value): static;

    /**
     * @deprecated  Use {@see whereJsonNotContainsAll()} instead.
     *               In the next major release, whereJsonNotContainsAll() will be renamed back to whereJsonNotContains()
     *               and the new behavior will be adopted.
     */
    public function whereJsonNotContains(string $expr, mixed $value): static;

    /**
     * Arrays must have all elements contained, other values are treated as a single element.
     */
    public function whereJsonNotContainsAll(string $expr, mixed $value): static;

    /**
     * @deprecated  Use {@see havingJsonContainsAll()} instead.
     *               In the next major release, havingJsonContainsAll() will be renamed back to havingJsonContains()
     *               and the new behavior will be adopted.
     */
    public function havingJsonContains(string $expr, mixed $value): static;

    /**
     * Arrays must have all elements contained, other values are treated as a single element.
     */
    public function havingJsonContainsAll(string $expr, mixed $value): static;

    /**
     * @deprecated  Use {@see havingJsonNotContainsAll()} instead.
     *               In the next major release, havingJsonNotContainsAll() will be renamed back to havingJsonNotContains()
     *               and the new behavior will be adopted.
     */
    public function havingJsonNotContains(string $expr, mixed $value): static;

    /**
     * Arrays must have all elements contained, other values are treated as a single element.
     */
    public function havingJsonNotContainsAll(string $expr, mixed $value): static;

    public function selectJsonLength(string $expr, string $as = ''): static;

    public function whereJsonLength(array|string $expr, mixed ...$args): static;

    public function whereJsonExists(string $expr): static;

    public function whereJsonNotExists(string $expr): static;

    public function havingJsonExists(string $expr): static;

    public function havingJsonNotExists(string $expr): static;

    public function jsonSet(string $expr, mixed $value): RawWrapper;

    public function jsonRemove(string $expr): RawWrapper;

    public function setJson(string $expr, mixed $value): static;

    public function setJsonRemove(string $expr): static;
}
