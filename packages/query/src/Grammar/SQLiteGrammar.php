<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\Query\expr;
use function Windwalker\Query\qn;

/**
 * The MySQLGrammar class.
 */
class SQLiteGrammar extends BaseGrammar implements JsonGrammarInterface
{
    use JsonGrammarTrait;

    /**
     * @var string
     */
    public static string $name = 'SQLite';

    /**
     * @var array
     */
    public static array $nameQuote = ['`', '`'];

    public function compileUpsert(
        Query $query,
        string $table,
        array $data,
        array $keys,
        array $updateFields,
        ?string $returningIdField = null,
    ): string {
        $table = (string) $query->quoteName($table);
        [$columns, $values] = $this->compileUpsertColumnsAndValues($query, $data);

        $returning = '';

        if ($returningIdField) {
            $returning = 'RETURNING ' . $query->quoteName($returningIdField);
        }

        $conflictKeys = $query->clause('()', $query->qnMultiple($keys), ',');
        $setActions = $query->clause('', [], ',');

        foreach ($query->qnMultiple($updateFields) as $key) {
            $setActions->append("$key = excluded.$key");
        }

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
        bool $instant = false
    ): Clause {
        $expr = expr('JSON_EXTRACT()', qn($column, $query));

        $expr->append($query->valueize(static::compileJsonPath($paths), $instant));

        return $expr;
    }

    public function compileJsonContains(
        Query $query,
        string $column,
        array $paths,
        string $value,
        bool $not = false
    ): Clause {
        throw new \LogicException('SQLite does not supports JSON contains now.');
    }

    public function compileJsonLength(Query $query, string $column, array $paths): Clause
    {
        throw new \LogicException('SQLite does not supports JSON length now.');
    }
}
