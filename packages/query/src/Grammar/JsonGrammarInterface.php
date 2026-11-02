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
        bool $instant = false
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
}
