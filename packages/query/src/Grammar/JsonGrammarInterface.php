<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

/**
 * Interface JsonGrammarInterface
 */
interface JsonGrammarInterface
{
    public function compileJsonSelector(
        Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false,
        ?string $cast = null
    ): Clause;

    /**
     * @deprecated  Use {@see compileJsonContainsAll()} instead.
     *               In the next major release, compileJsonContainsAll() will be renamed back
     *               to compileJsonContains() and the new behavior will be adopted.
     */
    public function compileJsonContains(
        Query $query,
        string $column,
        array $paths,
        string $value,
        bool $not = false
    ): Clause;

    /**
     * Arrays must have all elements contained, other values are treated as a single element.
     */
    public function compileJsonContainsAll(
        Query $query,
        string $column,
        array $paths,
        mixed $value,
        bool $not = false
    ): Clause;

    public function compileJsonLength(
        Query $query,
        string $column,
        array $paths
    ): Clause;

    /**
     * Whether the length expression can be used in a SELECT list.
     */
    public function supportsJsonSelectLength(): bool;

    public function compileJsonExists(
        Query $query,
        string $column,
        array $paths,
        bool $not = false
    ): Clause;

    public function compileJsonSet(
        Query $query,
        string $column,
        array $paths,
        mixed $value
    ): Clause;

    public function compileJsonRemove(
        Query $query,
        string $column,
        array $paths
    ): Clause;
}
