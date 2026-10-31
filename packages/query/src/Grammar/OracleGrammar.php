<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use InvalidArgumentException;
use LogicException;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The OracleGrammar class.
 */
class OracleGrammar extends BaseGrammar
{
    /**
     * @var string
     */
    public static string $name = 'Oracle';

    /**
     * @var string
     */
    public static string $nullDate = 'RRRR-MM-DD HH24:MI:SS';

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

        foreach ($updateFields as $k) {
            $v = $data[$k];

            if ($v instanceof RawWrapper) {
                $updateSet->append($query->quoteName($k) . ' = ' . $v());
            } else {
                $query->bind('update_set_' . $k, $v);
                $updateSet->append($query->quoteName($k) . " = :update_set_$k");
            }
        }

        // DUAL is the Oracle dummy table.
        return "MERGE INTO $table USING DUAL ON $srcOn
                    WHEN NOT MATCHED THEN INSERT $columns VALUES $values
                    WHEN MATCHED THEN UPDATE SET $updateSet";
    }

    /**
     * @inheritDoc
     */
    public function compileLimit(Query $query, array $sql): array
    {
        $limit = (int) $query->getLimit();
        $offset = (int) $query->getOffset();

        // Check if we need to mangle the query.
        if ($limit || $offset) {
            $start = 'SELECT windwalker2.*
                      FROM (
                          SELECT windwalker1.*, ROWNUM AS windwalker_db_rownum
                          FROM (';

            $end = ') windwalker1
            ) windwalker2';

            // Check if the limit value is greater than zero.
            if ($limit > 0) {
                $end .= ' WHERE windwalker2.windwalker_db_rownum BETWEEN '
                    . ($offset + 1) . ' AND ' . ($offset + $limit);
            } elseif ($offset) {
                $end .= ' WHERE windwalker2.windwalker_db_rownum > ' . ($offset + 1);
            }

            $sql = array_merge(
                ['rownum_start' => $start],
                $sql,
                ['rownum_end' => $end]
            );
        }

        return $sql;
    }
}
