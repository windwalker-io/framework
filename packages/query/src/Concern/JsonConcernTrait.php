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
use Windwalker\Query\Grammar\MySQLGrammar;

/**
 * Trait JsonConcernTrait
 */
trait JsonConcernTrait
{
    /**
     * parseJsonExtract
     *
     * @param  string  $expr
     * @param  bool    $instant
     *
     * @return  Clause
     *
     * @since  3.5.21
     */
    public function jsonSelector(string $expr, bool $instant = false): Clause
    {
        $unQuoteLast = str_contains($expr, '->>');

        $paths = array_filter(array_map('trim', preg_split('/->+/', $expr)), 'strlen');
        $paths = array_map(fn($segment) => trim($segment, "'"), $paths);

        $column = array_shift($paths);
        $column = $this->prependPrimaryAlias($column);

        return $this->getGrammar()->compileJsonSelector($this, $column, $paths, $unQuoteLast, $instant);
    }

    /**
     * whereJsonContains
     *
     * @param  string  $column
     * @param  mixed   $json
     * @param  string  $path
     *
     * @return  $this
     *
     * @throws \JsonException
     */
    public function whereJsonContains(string $column, mixed $json, string $path = '$'): static
    {
        if (!$this->getGrammar() instanceof MySQLGrammar) {
            throw new \RuntimeException(__METHOD__ . '() only support MySQL now.');
        }

        if (!is_json($json)) {
            $json = json_encode((array) $json, JSON_THROW_ON_ERROR);
        }

        return $this->whereRaw(
            $this->expr(
                'JSON_CONTAINS()',
                $column,
                $this->valueize($json, false),
                $this->valueize($path, false)
            ),
        );
    }

    public function whereJsonNotContains(string $column, mixed $json, string $path = '$'): static
    {
        if (!$this->getGrammar() instanceof MySQLGrammar) {
            throw new \RuntimeException(__METHOD__ . '() only support MySQL now.');
        }

        if (!is_json($json)) {
            $json = json_encode((array) $json, JSON_THROW_ON_ERROR);
        }

        return $this->whereRaw(
            $this->expr(
                'NOT JSON_CONTAINS()',
                $column,
                $this->valueize($json, false),
                $this->valueize($path, false)
            ),
        );
    }

    public function havingJsonContains(string $column, mixed $json, string $path = '$'): static
    {
        if (!$this->getGrammar() instanceof MySQLGrammar) {
            throw new \RuntimeException(__METHOD__ . '() only support MySQL now.');
        }

        if (!is_json($json)) {
            $json = json_encode((array) $json, JSON_THROW_ON_ERROR);
        }

        return $this->havingRaw(
            $this->expr(
                'JSON_CONTAINS()',
                $column,
                $this->valueize($json, false),
                $this->valueize($path, false)
            ),
        );
    }

    public function havingJsonNotContains(string $column, mixed $json, string $path = '$'): static
    {
        if (!$this->getGrammar() instanceof MySQLGrammar) {
            throw new \RuntimeException(__METHOD__ . '() only support MySQL now.');
        }

        if (!is_json($json)) {
            $json = json_encode((array) $json, JSON_THROW_ON_ERROR);
        }

        return $this->havingRaw(
            $this->expr(
                'NOT JSON_CONTAINS()',
                $column,
                $this->valueize($json, false),
                $this->valueize($path, false)
            ),
        );
    }
}
