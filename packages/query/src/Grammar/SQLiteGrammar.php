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
