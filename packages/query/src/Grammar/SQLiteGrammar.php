<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\Query\clause;
use function Windwalker\Query\expr;
use function Windwalker\Query\qn;

/**
 * The MySQLGrammar class.
 */
class SQLiteGrammar extends BaseGrammar implements JsonGrammarInterface
{
    use JsonGrammarTrait;
    public function compileMaterializedHint(?bool $materialized): string
    {
        return match ($materialized) {
            true => 'MATERIALIZED',
            false => 'NOT MATERIALIZED',
            null => '',
        };
    }

    /**
     * @var  string
     */
    public static string $name = 'SQLite';

    /**
     * @var  array
     */
    public static array $nameQuote = ['`', '`'];

    public function compileRandomOrder(): string
    {
        return 'RANDOM()';
    }

    public function compileUpsert(Query $query): string
    {
        [
            'table' => $table,
            'data' => $data,
            'keys' => $keys,
            'update' => $update,
            'returning' => $returning,
        ] = $this->resolveUpsert($query);
        [$columns, $values] = $this->compileUpsertColumnsAndValues($query, $data);

        $conflictKeys = $query->clause('()', $keys, ',');
        $setActions = $query->clause('', [], ',');

        foreach ($update as $key => $value) {
            $setActions->append("$key = " . ($value ?? "excluded.$key"));
        }

        $returning = $returning ? "RETURNING $returning" : '';

        return "INSERT INTO $table $columns
                VALUES $values
                ON CONFLICT $conflictKeys DO UPDATE SET $setActions
                $returning";
    }

    public function compileJsonSelector(
        Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false,
        ?string $cast = null
    ): Clause {
        $expr = expr('JSON_EXTRACT()', qn($column, $query));

        $expr->append($query->valueize(static::compileJsonPath($paths), $instant));

        if ($cast !== null) {
            $expr = match (static::normalizeJsonCast($cast)) {
                'int' => clause('CAST()', [$expr, 'AS INTEGER'], ' '),
                'float', 'decimal' => clause('CAST()', [$expr, 'AS REAL'], ' '),
                'string' => clause('CAST()', [$expr, 'AS TEXT'], ' '),
                'datetime' => expr('DATETIME()', $expr),
                'date' => expr('DATE()', $expr),
            };
        }

        return $expr;
    }

    /**
     * @deprecated  Use {@see compileJsonContainsAll()} instead.
     *               In the next major release, compileJsonContainsAll() will be renamed back
     *               to compileJsonContains() and the new behavior will be adopted.
     * @codeCoverageIgnore
     */
    public function compileJsonContains(
        Query $query,
        string $column,
        array $paths,
        string $value,
        bool $not = false
    ): Clause {
        throw new \LogicException('SQLite does not supports JSON contains now.');
    }

    public function compileJsonContainsAll(
        Query $query,
        string $column,
        array $paths,
        mixed $value,
        bool $not = false
    ): Clause {
        throw new \LogicException('SQLite does not supports JSON contains now.');
    }

    public function compileJsonLength(Query $query, string $column, array $paths): Clause
    {
        throw new \LogicException('SQLite does not supports JSON length now.');
    }

    public function compileJsonExists(Query $query, string $column, array $paths, bool $not = false): Clause
    {
        return clause(
            '',
            [
                $this->compileJsonPathFunction($query, 'JSON_TYPE', $column, $paths),
                $not ? 'IS NULL' : 'IS NOT NULL',
            ],
            ' '
        );
    }

    public function compileJsonSet(Query $query, string $column, array $paths, mixed $value): Clause
    {
        return $this->compileJsonPathFunction(
            $query,
            'JSON_SET',
            $column,
            $paths,
            $value instanceof RawWrapper
                ? $value
                : expr('JSON()', $query->valueize(static::encodeJsonValue($value), false))
        );
    }

    public function compileJsonRemove(Query $query, string $column, array $paths): Clause
    {
        return $this->compileJsonPathFunction($query, 'JSON_REMOVE', $column, $paths);
    }
}
