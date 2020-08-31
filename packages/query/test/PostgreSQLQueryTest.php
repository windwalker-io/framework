<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;

/**
 * The PostgresqlQueryTest class.
 */
class PostgreSQLQueryTest extends QueryTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLimitOffset()
    {
        // Limit
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5);

        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 5',
            $q->render()
        );

        // Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->offset(10);

        // Only offset will not work
        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" OFFSET 10',
            $q->render()
        );

        // Limit & Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(15);

        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 5 OFFSET 15',
            $q->render()
        );
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new PostgreSQLGrammar();
    }
}
