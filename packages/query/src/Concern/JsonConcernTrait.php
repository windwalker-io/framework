<?php

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\JsonGrammarInterface;

/**
 * Trait JsonConcernTrait
 */
trait JsonConcernTrait
{
    protected function getJsonGrammar(): AbstractGrammar&JsonGrammarInterface
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
     * Parse `column->path->>last` expression.
     *
     * Segments may be wrapped by single quotes (stripped) or double quotes (kept as is).
     * Arrow signs inside quotes are treated as part of the segment.
     *
     * @param  string  $expr
     *
     * A `::type` suffix after the last segment is returned as cast type.
     *
     * @return  array{ 0: string, 1: string[], 2: bool, 3: ?string }
     *                                                                Column, paths, whether the last arrow is `->>` (always true if cast), and cast type.
     */
    protected function splitColumnAndPaths(string $expr): array
    {
        $length = strlen($expr);
        $tokens = [];
        $current = '';
        $unQuoteLast = false;
        $cast = null;
        $i = 0;

        while ($i < $length) {
            $char = $expr[$i];

            if (($char === "'" || $char === '"') && trim($current) === '' && count($tokens) > 0) {
                $end = strpos($expr, $char, $i + 1);

                if ($end === false) {
                    throw new \InvalidArgumentException(
                        sprintf('Unclosed quote in JSON expression: %s', $expr)
                    );
                }

                $quoted = substr($expr, $i + 1, $end - $i - 1);
                $current = $char === '"' ? '"' . $quoted . '"' : $quoted;
                $i = $end + 1;

                continue;
            }

            if ($char === ':' && ($expr[$i + 1] ?? '') === ':' && count($tokens) > 0) {
                if (!preg_match('/\G::([A-Za-z_]\w*)\s*$/', $expr, $matches, 0, $i)) {
                    throw new \InvalidArgumentException(
                        sprintf('JSON cast must be the last part of expression: %s', $expr)
                    );
                }

                $cast = strtolower($matches[1]);
                $i = $length;

                continue;
            }

            if ($char === '-' && ($expr[$i + 1] ?? '') === '>') {
                $tokens[] = trim($current);
                $current = '';
                $unQuoteLast = ($expr[$i + 2] ?? '') === '>';
                $i += $unQuoteLast ? 3 : 2;

                continue;
            }

            $current .= $char;
            $i++;
        }

        $tokens[] = trim($current);

        $column = $tokens[0];
        $paths = array_values(array_filter(array_slice($tokens, 1), 'strlen'));

        if ($column === '') {
            throw new \InvalidArgumentException(
                sprintf('JSON expression must start with a column name: %s', $expr)
            );
        }

        if ($cast !== null && $paths === []) {
            throw new \InvalidArgumentException(
                sprintf('JSON cast requires at least one path: %s', $expr)
            );
        }

        return [
            $this->prependPrimaryAlias($column),
            $paths,
            ($unQuoteLast && $paths !== []) || $cast !== null,
            $cast,
        ];
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

        [$column, $paths, $unQuoteLast, $cast] = $this->splitColumnAndPaths($expr);

        return $grammar->compileJsonSelector($this, $column, $paths, $unQuoteLast, $instant, $cast);
    }

    /**
     * @param  string  $expr
     * @param  mixed   $value
     * @param  bool    $not
     * @param  bool    $legacy  Use the deprecated {@see JsonGrammarInterface::compileJsonContains()}.
     *
     * @return  Clause
     */
    protected function compileJsonContainsClause(string $expr, mixed $value, bool $not, bool $legacy = false): Clause
    {
        $grammar = $this->getJsonGrammar();

        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $legacy
            ? $grammar->compileJsonContains($this, $column, $paths, $value, $not)
            : $grammar->compileJsonContainsAll($this, $column, $paths, $value, $not);
    }

    /**
     * @throws  \JsonException
     *
     * @deprecated  Use {@see whereJsonContainsAll()} instead.
     *               In the next major release, whereJsonContainsAll() will be renamed back to whereJsonContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function whereJsonContains(string $expr, mixed $value): static
    {
        return $this->whereRaw($this->compileJsonContainsClause($expr, $value, false, true));
    }

    public function whereJsonContainsAll(string $expr, mixed $value): static
    {
        return $this->whereRaw($this->compileJsonContainsClause($expr, $value, false));
    }

    /**
     * @deprecated  Use {@see whereJsonNotContainsAll()} instead.
     *               In the next major release, whereJsonNotContainsAll() will be renamed back to whereJsonNotContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function whereJsonNotContains(string $expr, mixed $value): static
    {
        return $this->whereRaw($this->compileJsonContainsClause($expr, $value, true, true));
    }

    public function whereJsonNotContainsAll(string $expr, mixed $value): static
    {
        return $this->whereRaw($this->compileJsonContainsClause($expr, $value, true));
    }

    /**
     * @deprecated  Use {@see havingJsonContainsAll()} instead.
     *               In the next major release, havingJsonContainsAll() will be renamed back to havingJsonContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function havingJsonContains(string $expr, mixed $value): static
    {
        return $this->havingRaw($this->compileJsonContainsClause($expr, $value, false, true));
    }

    public function havingJsonContainsAll(string $expr, mixed $value): static
    {
        return $this->havingRaw($this->compileJsonContainsClause($expr, $value, false));
    }

    /**
     * @deprecated  Use {@see havingJsonNotContainsAll()} instead.
     *               In the next major release, havingJsonNotContainsAll() will be renamed back to havingJsonNotContains()
     *               and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function havingJsonNotContains(string $expr, mixed $value): static
    {
        return $this->havingRaw($this->compileJsonContainsClause($expr, $value, true, true));
    }

    public function havingJsonNotContainsAll(string $expr, mixed $value): static
    {
        return $this->havingRaw($this->compileJsonContainsClause($expr, $value, true));
    }

    public function selectJsonLength(string $expr, string $as = ''): static
    {
        $grammar = $this->getJsonGrammar();

        if (!$grammar->supportsJsonSelectLength()) {
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

    public function whereJsonExists(string $expr): static
    {
        return $this->whereRaw($this->compileJsonExistsClause($expr, false));
    }

    public function whereJsonNotExists(string $expr): static
    {
        return $this->whereRaw($this->compileJsonExistsClause($expr, true));
    }

    public function havingJsonExists(string $expr): static
    {
        return $this->havingRaw($this->compileJsonExistsClause($expr, false));
    }

    public function havingJsonNotExists(string $expr): static
    {
        return $this->havingRaw($this->compileJsonExistsClause($expr, true));
    }

    protected function compileJsonExistsClause(string $expr, bool $not): Clause
    {
        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->getJsonGrammar()->compileJsonExists($this, $column, $paths, $not);
    }

    /**
     * Set a value at JSON path of a column, used in UPDATE queries.
     * Value will be encoded to JSON, so arrays and objects are supported.
     */
    public function setJson(string $expr, mixed $value): static
    {
        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->setRaw(
            $this->clause(
                '',
                [
                    $this->quoteName($column),
                    '=',
                    $this->getJsonGrammar()->compileJsonSet($this, $column, $paths, $value),
                ]
            )
        );
    }

    /**
     * Remove a JSON path from a column, used in UPDATE queries.
     */
    public function setJsonRemove(string $expr): static
    {
        [$column, $paths] = $this->splitColumnAndPaths($expr);

        return $this->setRaw(
            $this->clause(
                '',
                [
                    $this->quoteName($column),
                    '=',
                    $this->getJsonGrammar()->compileJsonRemove($this, $column, $paths),
                ]
            )
        );
    }
}
