<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\Query\clause;
use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The MySQLGrammar class.
 */
class MySQLGrammar extends AbstractGrammar implements JsonGrammarInterface
{
    use JsonGrammarTrait;

    /**
     * @var  string
     */
    protected static string $name = 'MySQL';

    /**
     * @var  array
     */
    public static array $nameQuote = ['`', '`'];

    /**
     * @var  string
     */
    public static string $nullDate = '1000-01-01 00:00:00';

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

        $updateActions = $query->clause('', [], ',');

        foreach ($query->qnMultiple($updateFields) as $key) {
            $updateActions->append("$key = VALUES($key)");
        }

        if ($returningIdField) {
            $quotedReturningIdField = $query->quoteName($returningIdField);
            $updateActions->append("$quotedReturningIdField = LAST_INSERT_ID($quotedReturningIdField)");
        }

        return <<<SQL
        INSERT INTO $table $columns
        VALUES $values
        ON DUPLICATE KEY UPDATE
            $updateActions
        SQL;
    }

    public function compileOrderByField(Query $query, string $column, array $values): Clause
    {
        $bound = array_map(fn($v) => $query->valueize($v, false), $values);
        $field = static::clause('FIELD()', [$column, ...$bound], ', ');
        $nullIf = static::clause('NULLIF()', [$field, 0], ', ');

        return static::clause('COALESCE()', [$nullIf, count($values) + 1], ', ');
    }

    /**
     * If no connection set, we escape it with default function.
     *
     * Since mysql_real_escape_string() has been deprecated, we use an alternative one.
     * Please see:
     * http://stackoverflow.com/questions/4892882/mysql-real-escape-string-for-multibyte-without-a-connection
     *
     * @param  string  $text
     *
     * @return  string
     */
    public static function localEscape(string $text): string
    {
        return str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $text
        );
    }

    /**
     * @inheritDoc
     */
    public function listViews(?string $schema = null): Query
    {
        $query = $this->createQuery()
            ->select('TABLE_NAME')
            ->from('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_TYPE', 'VIEW');

        if ($schema !== null) {
            $query->where('TABLE_SCHEMA', $schema);
        } else {
            $query->where('TABLE_SCHEMA', raw('(SELECT DATABASE())'));
        }

        return $query;
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

        if ($unQuoteLast) {
            $expr = expr('JSON_UNQUOTE()', $expr);
        }

        if ($cast !== null) {
            $type = match (static::normalizeJsonCast($cast)) {
                'int' => 'SIGNED',
                'float' => 'DOUBLE',
                'decimal' => 'DECIMAL(65, 30)',
                'string' => 'CHAR',
                'datetime' => 'DATETIME',
                'date' => 'DATE',
            };

            $expr = clause('CAST()', [$expr, "AS $type"], ' ');
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
        $value = static::normalizeLegacyJsonContainsValue($value);

        return $query->expr(
            $not
                ? 'NOT JSON_CONTAINS()'
                : 'JSON_CONTAINS()',
            qn($column, $query),
            $query->valueize($value, false),
            $query->valueize(static::compileJsonPath($paths), false)
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

        return $query->expr(
            $not
                ? 'NOT JSON_CONTAINS()'
                : 'JSON_CONTAINS()',
            qn($column, $query),
            $query->valueize($value, false),
            $query->valueize(static::compileJsonPath($paths), false)
        );
    }

    public function compileJsonLength(Query $query, string $column, array $paths): Clause
    {
        return $query->expr(
            'IFNULL()',
            $this->compileJsonPathFunction($query, 'JSON_LENGTH', $column, $paths),
            0
        );
    }

    public function compileJsonExists(Query $query, string $column, array $paths, bool $not = false): Clause
    {
        return $query->expr(
            $not ? 'NOT JSON_CONTAINS_PATH()' : 'JSON_CONTAINS_PATH()',
            qn($column, $query),
            raw("'one'"),
            $query->valueize(static::compileJsonPath($paths), false)
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
                : clause('CAST()', [$query->valueize(static::encodeJsonValue($value), false), 'AS JSON'], ' ')
        );
    }

    public function compileJsonRemove(Query $query, string $column, array $paths): Clause
    {
        return $this->compileJsonPathFunction($query, 'JSON_REMOVE', $column, $paths);
    }
}
