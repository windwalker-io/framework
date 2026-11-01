<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use InvalidArgumentException;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The OracleGrammar class.
 */
class OracleGrammar extends BaseGrammar implements JsonGrammarInterface
{
    use JsonGrammarTrait;

    /**
     * Oracle folds unquoted identifiers to upper case, so names are upper-cased
     * to match objects that were created without quotes.
     */
    public static function quoteName(string|\Stringable $name, bool $ignoreDot = false): string
    {
        if (!$name instanceof RawWrapper) {
            $name = strtoupper((string) $name);
        }

        return parent::quoteName($name, $ignoreDot);
    }

    public function supportsTableAliasAs(): bool
    {
        return false;
    }

    protected function supportsRecursiveKeyword(): bool
    {
        return false;
    }

    /**
     * @var  string
     */
    public static string $name = 'Oracle';

    /**
     * @var  string
     */
    public static string $nullDate = 'RRRR-MM-DD HH24:MI:SS';

    public function compileInsert(Query $query): string
    {
        $values = $query->getValues();

        // Multi-row VALUES with bound params fails in pdo_oci (ORA-01722) and INSERT ALL
        // does not work with identity columns, so use INSERT ... SELECT ... FROM DUAL UNION ALL.
        if (
            !$query->getSet()
            && $values instanceof Clause
            && count($values->getElements()) > 1
        ) {
            $selects = array_map(
                static fn($row) => 'SELECT ' . substr((string) $row, 1, -1) . ' FROM DUAL',
                $values->getElements()
            );

            $sql = [$query->getInsert()];

            if ($columns = $query->getColumns()) {
                $sql[] = $columns;
            }

            $sql[] = implode(' UNION ALL ', $selects);

            return trim(implode(' ', $sql));
        }

        return parent::compileInsert($query);
    }

    /**
     * Oracle MERGE cannot return the ID, so $returningIdField is ignored.
     */
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

        $srcOn = $query->clause('()', [], ' AND ');

        foreach ($keys as $key) {
            $srcOn->append($query->quoteName($key) . " = :key_$key");
            $query->bind(
                "key_$key",
                $data[$key]
                ?? throw new InvalidArgumentException("Key field: $key not exists in data.")
            );
        }

        $updateSet = $query->clause('', [], ',');

        // Oracle forbids updating columns referenced in the ON clause.
        foreach (array_diff($updateFields, $keys) as $k) {
            $v = $data[$k];

            if ($v instanceof RawWrapper) {
                $updateSet->append($query->quoteName($k) . ' = ' . $v());
            } else {
                $query->bind('update_set_' . $k, $v);
                $updateSet->append($query->quoteName($k) . " = :update_set_$k");
            }
        }

        // DUAL is the Oracle dummy table.
        $sql = "MERGE INTO $table USING DUAL ON $srcOn
                    WHEN NOT MATCHED THEN INSERT $columns VALUES $values";

        if (count($updateSet) > 0) {
            $sql .= " WHEN MATCHED THEN UPDATE SET $updateSet";
        }

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function compileRandomOrder(): string
    {
        return 'DBMS_RANDOM.VALUE';
    }

    /**
     * Uses the Oracle 12c+ row limiting clause.
     */
    public function compileLimit(Query $query, array $sql): array
    {
        $limit = $query->getLimit();
        $offset = $query->getOffset();

        if ($offset !== null && (int) $offset > 0) {
            $sql['offset'] = 'OFFSET ' . (int) $offset . ' ROWS';
        }

        if ($limit !== null) {
            $sql['limit'] = 'FETCH NEXT ' . (int) $limit . ' ROWS ONLY';
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
        // Oracle requires JSON paths to be literals, so they can never be bound.
        $vc = $query->valueize(static::compileJsonPath($paths), true);

        $expr = $unQuoteLast
            ? expr('JSON_VALUE()', $vc)
            : expr('JSON_QUERY()', $vc);

        return $expr->prepend(qn($column, $query));
    }

    public function compileJsonContains(
        Query $query,
        string $column,
        array $paths,
        string $value,
        bool $not = false
    ): Clause {
        $path = static::compileJsonPath($paths) . '[*]';

        return $query->expr(
            $not ? 'NOT EXISTS()' : 'EXISTS()',
            $query->createSubQuery()
                ->selectRaw(1)
                ->from(
                    raw(
                        'JSON_TABLE(' . qn($column, $query) . ', ' . $query->valueize($path, true)
                        . " COLUMNS (\"VALUE\" VARCHAR2(4000) PATH '$'))"
                    )
                )
                ->where('value', $value)
        );
    }

    public function compileJsonLength(Query $query, string $column, array $paths): Clause
    {
        $col = (string) qn($column, $query);
        $path = str_replace("'", "''", static::compileJsonPath($paths));
        $columns = "COLUMNS (\"V\" VARCHAR2(1) PATH '$')";

        $count = static fn(string $suffix) => "(SELECT COUNT(*) FROM JSON_TABLE($col, '$path$suffix' $columns))";

        return new Clause(
            'CASE',
            [
                "WHEN JSON_VALUE($col, '$path.type()') = 'object'",
                'THEN ' . $count('.*'),
                'ELSE ' . $count('[*]') . ' END',
            ],
            ' '
        );
    }
}
