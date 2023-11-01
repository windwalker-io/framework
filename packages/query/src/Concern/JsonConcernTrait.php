<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\JsonGrammarInterface;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;

/**
 * Trait JsonConcernTrait
 */
trait JsonConcernTrait
{
    protected function getJsonGrammar(): AbstractGrammar & JsonGrammarInterface
    {
        $grammar = $this->getGrammar();

        if (!$grammar instanceof JsonGrammarInterface) {
            throw new \LogicException(
                sprintf(
                    '%s not supports JSON grammar now',
                    static::class
                )
            );
        }

        return $grammar;
    }

    /**
     * @param  string  $expr
     *
     * @return  array{ 0: string, 1: string[] }
     */
    protected function splitColumnAndPaths(string $expr): array
    {
        $paths = array_filter(array_map('trim', preg_split('/->+/', $expr)), 'strlen');
        $paths = array_map(static fn($segment) => trim($segment, "'"), $paths);

        $column = array_shift($paths);
        $column = $this->prependPrimaryAlias($column);

        return [$column, $paths];
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
        $grammar = $this->getJsonGrammar();

        $unQuoteLast = str_contains($expr, '->>');

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $grammar->compileJsonSelector($this, $column, $paths, $unQuoteLast, $instant);
    }

    /**
     * @param  string  $expr
     * @param  mixed   $value
     *
     * @return  $this
     *
     * @throws \JsonException
     */
    public function whereJsonContains(string $expr, mixed $value): static
    {
        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->whereRaw(
            $grammar->compileJsonContains($this, $column, $paths, $value),
        );
    }

    public function whereJsonNotContains(string $expr, mixed $value): static
    {
        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->whereRaw(
            $grammar->compileJsonContains($this, $column, $paths, $value, true),
        );
    }

    public function havingJsonContains(string $expr, mixed $value): static
    {
        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->havingRaw(
            $grammar->compileJsonContains($this, $column, $paths, $value),
        );
    }

    public function havingJsonNotContains(string $expr, mixed $value): static
    {
        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->havingRaw(
            $grammar->compileJsonContains($this, $column, $paths, $value, true),
        );
    }

    public function selectJsonLength(string $expr, string $as = ''): static
    {
        $grammar = $this->getJsonGrammar();

        if ($grammar instanceof SQLServerGrammar) {
            throw new \LogicException($grammar::class . ' does not supports selectJsonLength()');
        }

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->selectAs(
            $grammar->compileJsonLength($this, $column, $paths),
            $as
        );
    }

    public function whereJsonLength(string|array $expr, mixed ...$args): static
    {
        if (is_array($expr)) {
            foreach ($expr as $item) {
                $this->whereJsonLength(...(array) $item);
            }

            return $this;
        }

        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->where(
            $grammar->compileJsonLength($this, $column, $paths),
            ...$args
        );
    }
}
