<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Concern;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;
use Windwalker\Test\Traits\QueryTestTrait;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class JsonConcernTraitTest extends TestCase
{
    use QueryTestTrait;

    private static ?MockEscaper $escaper = null;

    private static function query(AbstractGrammar $grammar): Query
    {
        return new Query(self::$escaper ??= new MockEscaper(), $grammar)->select('*')->from('t');
    }

    #[Test]
    public function mysqlJsonHelpersCompile(): void
    {
        $g = new MySQLGrammar();

        self::assertSqlEquals(
            "SELECT * FROM `t` HAVING JSON_CONTAINS(`data`, '[\"x\"]', '$.a')",
            self::query($g)->havingJsonContainsAll('data->a', 'x')
        );
        self::assertSqlEquals(
            "SELECT * FROM `t` HAVING NOT JSON_CONTAINS(`data`, '[\"x\"]', '$.a')",
            self::query($g)->havingJsonNotContainsAll('data->a', 'x')
        );
        self::assertSqlEquals(
            "SELECT *, IFNULL(JSON_LENGTH(`data`, '$.a'), 0) AS `len` FROM `t`",
            self::query($g)->selectJsonLength('data->a', 'len')
        );
        self::assertSqlEquals(
            "SELECT * FROM `t` WHERE IFNULL(JSON_LENGTH(`data`, '$.a'), 0) > 2 "
            . "AND IFNULL(JSON_LENGTH(`data`, '$.b'), 0) = 1",
            self::query($g)->whereJsonLength([['data->a', '>', 2], ['data->b', 1]])
        );
        self::assertSqlEquals(
            "SELECT * FROM `t` WHERE NOT JSON_CONTAINS(`data`, '[\"x\"]', '$.a')",
            self::query($g)->whereJsonNotContainsAll('data->a', 'x')
        );
    }

    #[Test]
    public function jsonContainsAllAcceptsArrayAndScalars(): void
    {
        self::assertSqlEquals(
            "SELECT * FROM `t` WHERE JSON_CONTAINS(`data`, '[\"a\",\"b\"]', '$.a')",
            self::query(new MySQLGrammar())->whereJsonContainsAll('data->a', ['a', 'b'])
        );
        self::assertSqlEquals(
            "SELECT * FROM `t` WHERE JSON_CONTAINS(`data`, '[1]', '$.a')",
            self::query(new MySQLGrammar())->whereJsonContainsAll('data->a', 1)
        );
        self::assertSqlEquals(
            'SELECT * FROM "t" WHERE ("data"::jsonb->\'a\' @> \'["a","b"]\')',
            self::query(new PostgreSQLGrammar())->whereJsonContainsAll('data->a', ['a', 'b'])
        );
        self::assertSqlEquals(
            "SELECT * FROM [t] WHERE (EXISTS(SELECT 1 FROM OPENJSON([data], '$.a') WHERE [value] = 'a')"
            . " AND EXISTS(SELECT 1 FROM OPENJSON([data], '$.a') WHERE [value] = 'b'))",
            self::query(new SQLServerGrammar())->whereJsonContainsAll('data->a', ['a', 'b'])->render(true)
        );
        self::assertSqlEquals(
            "SELECT * FROM `t` WHERE NOT JSON_CONTAINS(`data`, '[\"a\",\"b\"]', '$.a')",
            self::query(new MySQLGrammar())->whereJsonNotContainsAll('data->a', ['a', 'b'])
        );
    }

    #[Test]
    public function postgresqlJsonHelpersCompile(): void
    {
        $g = new PostgreSQLGrammar();

        self::assertSqlEquals(
            'SELECT * FROM "t" HAVING ("data"::jsonb->\'a\' @> \'["x"]\')',
            self::query($g)->havingJsonContainsAll('data->a', 'x')
        );
        self::assertSqlEquals(
            'SELECT * FROM "t" HAVING NOT ("data"::jsonb->\'a\' @> \'["x"]\')',
            self::query($g)->havingJsonNotContainsAll('data->a', 'x')
        );
        self::assertStringContainsString(
            'jsonb_array_length("data"::jsonb->\'a\') ELSE 0 END AS "len"',
            self::renderQuery(self::query($g)->selectJsonLength('data->a', 'len'))
        );
        self::assertStringContainsString(
            'jsonb_array_length',
            self::renderQuery(self::query($g)->whereJsonLength('data->a', '>', 2))
        );
    }

    #[Test]
    public function sqlServerJsonHelpersCompile(): void
    {
        $g = new SQLServerGrammar();

        self::assertSqlEquals(
            "SELECT * FROM [t] HAVING EXISTS(SELECT 1 FROM OPENJSON([data], '$.a') WHERE [value] = 'x')",
            self::query($g)->havingJsonContainsAll('data->a', 'x')
        );
        self::assertSqlEquals(
            "SELECT * FROM [t] HAVING NOT EXISTS(SELECT 1 FROM OPENJSON([data], '$.a') WHERE [value] = 'x')",
            self::query($g)->havingJsonNotContainsAll('data->a', 'x')
        );
        self::assertSqlEquals(
            "SELECT * FROM [t] WHERE ISNULL((SELECT COUNT(*) FROM OPENJSON([data], '$.a')), 0) = 2",
            self::query($g)->whereJsonLength('data->a', 2)
        );
    }

    #[Test]
    public function sqlServerDoesNotSupportSelectJsonLength(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('does not supports selectJsonLength()');

        self::query(new SQLServerGrammar())->selectJsonLength('data->a');
    }

    #[Test]
    public function jsonSelectorThrowsWhenGrammarHasNoJsonSupport(): void
    {
        $escaper = new MockEscaper();
        $query = new Query($escaper, new BaseGrammar());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('not supports JSON grammar');

        $query->jsonSelector('data->foo');
    }

    #[Test]
    public function whereJsonContainsAllThrowsWhenGrammarHasNoJsonSupport(): void
    {
        $escaper = new MockEscaper();
        $query = new Query($escaper, new BaseGrammar());

        $this->expectException(LogicException::class);

        $query->whereJsonContainsAll('data->foo', 'bar');
    }
}
