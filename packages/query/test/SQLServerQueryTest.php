<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;

use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The SqlsrvQueryTest class.
 */
class SQLServerQueryTest extends QueryTest implements QueryJsonTestInterface
{
    protected static array $nameQuote = ['[', ']'];

    public static function createGrammar(): AbstractGrammar
    {
        return new SQLServerGrammar();
    }

    /**
     * testParseJsonSelector
     *
     * @param  string  $selector
     * @param  bool    $unQuoteLast
     * @param  string  $expected
     *
     * @return  void
     *
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('parseJsonSelectorProvider')]
    #[Test]
    public function parseJsonSelectorConvertsToDbJsonPath(string $selector, string $expected): void
    {
        $parsed = $this->instance->jsonSelector($selector);

        $bounded = $this->instance->getMergedBounded();

        self::assertEquals(
            $expected,
            BoundedHelper::emulatePrepared(
                $this->instance->getEscaper(),
                (string) $parsed,
                $bounded
            )
        );
        $this->instance->render(true);
    }

    public static function parseJsonSelectorProvider(): array
    {
        return [
            [
                'foo ->> bar',
                'JSON_VALUE([foo], \'$.bar\')',
            ],
            [
                'foo->bar[1]->>yoo',
                'JSON_VALUE([foo], \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar[1]->>\'yoo\'',
                'JSON_VALUE([foo], \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar[1]->>"yoo"',
                'JSON_VALUE([foo], \'$.bar[1]."yoo"\')',
            ],
            [
                'foo->bar[1]->\'yoo\'',
                'JSON_QUERY([foo], \'$.bar[1].yoo\')',
            ],
        ];
    }

    #[Test]
    public function jsonQuoteWrapsJsonExpression(): void
    {
        $query = $this->instance->select('foo -> bar ->> yoo AS yoo')
            ->selectRaw('%n AS l', 'foo -> bar -> loo')
            ->from('test')
            ->where('foo -> bar ->> yoo', 'www')
            ->having('foo -> bar', '=', qn('hoo -> joo ->> moo'))
            ->order('foo -> bar ->> yoo', 'DESC');

        self::assertSqlEquals(
            <<<SQL
            SELECT JSON_VALUE([foo], '$.bar.yoo') AS [yoo], JSON_QUERY([foo], '$.bar.loo') AS l
            FROM [test] WHERE JSON_VALUE([foo], '$.bar.yoo') = 'www'
            HAVING JSON_QUERY([foo], '$.bar') = JSON_VALUE([hoo], '$.joo.moo')
            ORDER BY JSON_VALUE([foo], '$.bar.yoo') DESC
            SQL,
            $query->render(true)
        );
    }

    #[Test]
    public function jsonContainsBuildsContainsCondition(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonContainsAll('params -> foo ->> bar', 'yoo');

        self::assertSqlEquals(
            <<<SQL
            SELECT * FROM [articles] AS [a]
                LEFT JOIN [ww_categories] AS [c] ON [a].[category_id] = [c].[id]
                WHERE EXISTS(SELECT 1 FROM OPENJSON([a].[params], '$.foo.bar') WHERE [value] = 'yoo')
            SQL,
            $q->render(true)
        );
    }

    #[Test]
    public function jsonNotContainsBuildsNegatedCondition(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonNotContainsAll('params -> foo ->> bar', 'yoo');

        self::assertSqlEquals(
            <<<SQL
            SELECT * FROM [articles] AS [a]
                LEFT JOIN [ww_categories] AS [c] ON [a].[category_id] = [c].[id]
                WHERE NOT EXISTS(SELECT 1 FROM OPENJSON([a].[params], '$.foo.bar') WHERE [value] = 'yoo')
            SQL,
            $q->render(true)
        );
    }

    #[Test]
    public function jsonLengthBuildsLengthExpression(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonLength('params -> foo ->> bar', '>', 3);

        self::assertSqlEquals(
            <<<SQL
            SELECT * FROM [articles] AS [a]
                LEFT JOIN [ww_categories] AS [c] ON [a].[category_id] = [c].[id]
            WHERE ISNULL((SELECT COUNT(*) FROM OPENJSON([a].[params], '$.foo.bar')), 0) > 3
            SQL,
            $q->render(true)
        );
    }

    #[Test]
    public function insertBuildsInsertStatement(): void
    {
        $this->instance->insert('foo', true)
            ->columns('id', 'title', ['foo', 'bar'], 'yoo')
            ->values(
                [1, 'A', 'a', null, raw('CURRENT_TIMESTAMP()')],
                [2, 'B', 'b', null, raw('CURRENT_TIMESTAMP()')],
                [3, 'C', 'c', null, raw('CURRENT_TIMESTAMP()')]
            );

        self::assertSqlEquals(
            <<<SQL
SET IDENTITY_INSERT "foo" ON;
INSERT INTO "foo"
("id", "title", "foo", "bar", "yoo")
VALUES
    (1, 'A', 'a', NULL, CURRENT_TIMESTAMP()),
    (2, 'B', 'b', NULL, CURRENT_TIMESTAMP()),
    (3, 'C', 'c', NULL, CURRENT_TIMESTAMP())
; SET IDENTITY_INSERT "foo" OFF;
SQL
            ,
            $this->instance
        );

        $q = self::createQuery()
            ->insert('foo')
            ->set('id', 1)
            ->set(
                [
                    'title' => 'A',
                    'foo' => 'a',
                    'bar' => null,
                    'yoo' => raw('CURRENT_TIMESTAMP()'),
                ]
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" SET "id" = 1, "title" = 'A', "foo" = 'a', "bar" = NULL, "yoo" = CURRENT_TIMESTAMP()
SQL
            ,
            $q
        );

        $q = self::createQuery()
            ->insert('foo')
            ->columns('id', 'title')
            ->values(
                self::createQuery()
                    ->select('id', 'title')
                    ->from('articles'),
                self::createQuery()
                    ->select('id', 'title')
                    ->from('categories')
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" ("id", "title")
(SELECT "id", "title" FROM "articles") UNION (SELECT "id", "title" FROM "categories")
SQL
            ,
            $q
        );
    }

    #[Test]
    public function limitOffsetRendersLimitClause()
    {
        // Limit
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5);

        self::assertSqlEquals(
            'SELECT TOP 5 * FROM "foo" ORDER BY "id"',
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
            // phpcs:disable
            'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ( SELECT * FROM [foo] ORDER BY [id] ) AS A) AS A WHERE RowNumber > 10',
            // phpcs:enable
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
            // phpcs:disable
            'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM ( SELECT TOP 20 * FROM [foo] ORDER BY [id] ) AS A) AS A WHERE RowNumber > 15',
            // phpcs:enable
            $q->render()
        );
    }

    protected static function randomOrderSql(): string
    {
        return 'NEWID()';
    }

    #[Test]
    public function jsonCastSelector(): void
    {
        $query = $this->instance->select('a->n::int AS n')
            ->from('t')
            ->where('a->p::decimal', '>', 10)
            ->order("a->'x::y'::date");

        self::assertEquals(
            'SELECT CAST(JSON_VALUE([a], \'$.n\') AS BIGINT) AS [n] FROM [t] WHERE CAST(JSON_VALUE([a], \'$.p\') AS DECIMAL(38, 10)) > 10 ORDER BY CAST(JSON_VALUE([a], \'$."x::y"\') AS DATE)',
            $query->render(true)
        );
    }

    #[Test]
    public function jsonExists(): void
    {
        $query = $this->instance->select('*')
            ->from('t')
            ->whereJsonExists('a->b->1')
            ->whereJsonNotExists('a->c');

        self::assertEquals(
            'SELECT * FROM [t] WHERE JSON_PATH_EXISTS([a], \'$.b[1]\') = 1 AND JSON_PATH_EXISTS([a], \'$.c\') = 0',
            $query->render(true)
        );
    }

    #[Test]
    public function jsonSetAndRemove(): void
    {
        $query = $this->instance->update('t')
            ->setJson('a->b->c', ['x' => 1])
            ->setJson('a->d', 'str')
            ->setJsonRemove('a->e');

        self::assertEquals(
            'UPDATE [t] SET [a] = JSON_MODIFY([a], \'$.b.c\', JSON_QUERY(\'{"x":1}\')), [a] = JSON_MODIFY([a], \'$.d\', \'str\'), [a] = JSON_MODIFY([a], \'$.e\', NULL)',
            $query->render(true)
        );
    }

    #[Test]
    public function jsonCastAllTypes(): void
    {
        foreach (['int', 'integer', 'float', 'double', 'decimal', 'numeric', 'string', 'text', 'datetime', 'date'] as $type) {
            $sql = (clone $this->instance)->select('a->n::' . $type . ' AS n')->from('t')->render(true);

            self::assertStringNotContainsString('a->', $sql, $type);
        }
    }

    #[Test]
    public function jsonContainsRejectsEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->select('*')->from('t')->whereJsonContainsAll('a->b', [])->render(true);
    }

    #[Test]
    public function jsonContainsRejectsNonScalar(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->select('*')->from('t')->whereJsonContainsAll('a->b', [['x']])->render(true);
    }
}
