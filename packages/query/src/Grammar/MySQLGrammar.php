<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The MySQLGrammar class.
 */
class MySQLGrammar extends AbstractGrammar implements JsonGrammarInterface
{
    /**
     * @var string
     */
    protected static string $name = 'MySQL';

    /**
     * @var array
     */
    public static array $nameQuote = ['`', '`'];

    /**
     * @var string
     */
    public static string $nullDate = '1000-01-01 00:00:00';

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

    public static function compileJsonPath(array $segments): string
    {
        $path = '$';

        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $path .= "[$segment]";
            } else {
                $path .= '.' . $segment;
            }
        }

        return $path;
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

        if ($unQuoteLast) {
            $expr = expr('JSON_UNQUOTE()', $expr);
        }

        return $expr;
    }

    public function compileJsonContains(
        Query $query,
        string $column,
        array $paths,
        string $value,
        bool $not = false
    ): Clause {
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
            $query->expr(
                'JSON_LENGTH()',
                qn($column, $query),
                $query->valueize(static::compileJsonPath($paths), false)
            ),
            0
        );
    }
}
