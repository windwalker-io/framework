<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\SQLiteGrammar;

/**
 * The SqliteQueryTest class.
 */
class SqliteQueryTest extends QueryTest
{
    protected static array $nameQuote = ['`', '`'];

    public static function createGrammar(): AbstractGrammar
    {
        return new SQLiteGrammar();
    }
}
