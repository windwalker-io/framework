<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\Query\clause;
use function Windwalker\Query\qn;

/**
 * The PostgresqlGrammar class.
 */
class PostgreSQLGrammar extends AbstractGrammar implements JsonGrammarInterface
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
    public static string $name = 'PostgreSQL';

    /**
     * @var  string
     */
    public static string $nullDate = '1970-01-01 00:00:00';

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

        $conflictKeys = $query->clause('()', $query->qnMultiple($keys), ',');
        $updateSet = $query->clause('', [], ',');

        foreach ($query->qnMultiple($updateFields) as $key) {
            $updateSet->append("$key = EXCLUDED.$key");
        }

        $returning = '';

        if ($returningIdField) {
            $returning = 'RETURNING ' . $query->quoteName($returningIdField);
        }

        return <<<SQL
        INSERT INTO $table $columns
        VALUES $values ON CONFLICT $conflictKeys DO UPDATE SET
            $updateSet
        $returning
        SQL;
    }

    /**
     * @inheritDoc
     */
    public function compileRandomOrder(): string
    {
        return 'RANDOM()';
    }

    public function compileLimit(Query $query, array $sql): array
    {
        $limit = (int) $query->getLimit();
        $offset = (int) $query->getOffset();

        if ($limit > 0) {
            $sql['limit'] = 'LIMIT ' . $limit;
        }

        if ($offset > 0) {
            $sql['offset'] = 'OFFSET ' . $offset;
        }

        return $sql;
    }

    public function compileJsonSelector(
        Query $query,
        string $column,
        array $paths,
        bool $unQuoteLast = true,
        bool $instant = false
    ): Clause {
        $newPaths = [];

        foreach ($paths as $path) {
            if (is_numeric($path)) {
                $newPaths[] = $path;
            } else {
                $newPaths[] = $query->valueize($path, $instant);
            }
        }

        $last = array_pop($newPaths);
        $lastArrow = $unQuoteLast ? '->>' : '->';
        array_unshift($newPaths, qn($column, $query) . '::jsonb');

        return clause(
            '',
            [clause('', $newPaths, '->'), $last],
            $lastArrow
        );
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
        if (!is_json($value)) {
            $value = json_encode((array) $value, JSON_THROW_ON_ERROR);
        }

        return clause(
            $not ? 'NOT ()' : '()',
            [
                $this->compileJsonSelector($query, $column, $paths, false, false),
                '@>',
                $query->valueize($value, false),
            ],
            ' '
        );
    }

    public function compileJsonContainsAll(
        Query $query,
        string $column,
        array $paths,
        mixed $value,
        bool $not = false
    ): Clause {
        $value = static::encodeJsonContainsValue($value);

        return clause(
            $not ? 'NOT ()' : '()',
            [
                $this->compileJsonSelector($query, $column, $paths, false, false),
                '@>',
                $query->valueize($value, false),
            ],
            ' '
        );
    }

    public function compileJsonLength(Query $query, string $column, array $paths): Clause
    {
        $expr = (string) $this->compileJsonSelector($query, $column, $paths, false, true);

        return clause(
            'CASE',
            [
                "WHEN jsonb_typeof($expr) = 'object'",
                "THEN array_length(ARRAY (SELECT * FROM jsonb_object_keys($expr)), 1)",
                "WHEN jsonb_typeof($expr) = 'array'",
                "THEN jsonb_array_length($expr)",
                'ELSE 0 END'
            ],
            ' '
        );
    }
}
