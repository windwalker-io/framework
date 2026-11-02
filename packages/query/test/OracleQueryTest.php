<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\OracleGrammar;
use Windwalker\Query\Bounded\BoundedHelper;

use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The OracleQueryTest class.
 */
class OracleQueryTest extends QueryTest implements QueryJsonTestInterface
{
    public static function createGrammar(): AbstractGrammar
    {
        return new OracleGrammar();
    }

    protected static function replaceQn(string $sql): string
    {
        $sql = str_replace('\\"', '__QQ__', $sql);

        $sql = preg_replace_callback(
            '/"(\w+)"/',
            static fn(array $m) => '"' . strtoupper($m[1]) . '"',
            $sql
        );

        return static::dropAs(str_replace('__QQ__', '"', $sql));
    }

    /**
     * Oracle rejects AS before table aliases, so alias keywords are ignored when comparing.
     */
    protected static function renderQuery($query): string
    {
        return static::dropAs(parent::renderQuery($query));
    }

    #[DataProvider('asProvider')]
    #[Test]
    public function asQuotesValueWithAlias(string $expt, $value, $alias, bool $isColumn = true): void
    {
        self::assertEquals(
            static::replaceQn($expt),
            static::dropAs((string) $this->instance->as($value, $alias, $isColumn))
        );
    }

    protected static function dropAs(string $sql): string
    {
        return preg_replace('/ AS (?=")/', ' ', $sql);
    }

    #[Test]
    public function insertBuildsInsertStatement(): void
    {
        $this->instance->insert('foo', true)
            ->columns('id', 'title', ['foo', 'bar'], 'yoo')
            ->values(
                [1, 'A', 'a', null, raw('CURRENT_TIMESTAMP()')],
                [2, 'B', 'b', null, raw('CURRENT_TIMESTAMP()')]
            );

        self::assertSqlEquals(
            'INSERT INTO "FOO" ("ID", "TITLE", "FOO", "BAR", "YOO") '
            . "SELECT 1, 'A', 'a', NULL, CURRENT_TIMESTAMP() FROM DUAL UNION ALL "
            . "SELECT 2, 'B', 'b', NULL, CURRENT_TIMESTAMP() FROM DUAL",
            $this->instance
        );
    }

    #[Test]
    public function quoteNameQuotesIdentifier(): void
    {
        $this->assertEquals('"FOO"', $this->instance->quoteName('foo'));
        $this->assertEquals(['"FOO"'], $this->instance->qnMultiple(['foo']));
    }

    #[Test]
    public function stripNameQuoteRemovesIdentifierQuotes()
    {
        $quoteStr = $this->instance->quoteName('foo');

        self::assertEquals('FOO', $this->instance->stripNameQuote($quoteStr));
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

        self::assertSqlEquals('SELECT * FROM "FOO" ORDER BY "ID" FETCH NEXT 5 ROWS ONLY', $q->render());

        // Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->offset(10);

        self::assertSqlEquals('SELECT * FROM "FOO" ORDER BY "ID" OFFSET 10 ROWS', $q->render());

        // Limit & Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(15);

        self::assertSqlEquals(
            'SELECT * FROM "FOO" ORDER BY "ID" OFFSET 15 ROWS FETCH NEXT 5 ROWS ONLY',
            $q->render()
        );
    }

    protected static function randomOrderSql(): string
    {
        return 'DBMS_RANDOM.VALUE';
    }

    #[DataProvider('parseJsonSelectorProvider')]
    #[Test]
    public function parseJsonSelectorConvertsToDbJsonPath(string $selector, string $expected): void
    {
        $parsed = $this->instance->jsonSelector($selector);

        self::assertEquals(
            $expected,
            BoundedHelper::emulatePrepared(
                $this->instance->getEscaper(),
                (string) $parsed,
                $this->instance->getMergedBounded()
            )
        );
        $this->instance->render(true);
    }

    public static function parseJsonSelectorProvider(): array
    {
        return [
            ['foo ->> bar', 'JSON_VALUE("FOO", \'$.bar\')'],
            ['foo->bar[1]->>yoo', 'JSON_VALUE("FOO", \'$.bar[1].yoo\')'],
            ['foo->bar[1]->>\'yoo\'', 'JSON_VALUE("FOO", \'$.bar[1].yoo\')'],
            ['foo->bar[1]->>"yoo"', 'JSON_VALUE("FOO", \'$.bar[1]."yoo"\')'],
            ['foo->bar[1]->\'yoo\'', 'JSON_QUERY("FOO", \'$.bar[1].yoo\')'],
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
            SELECT JSON_VALUE("foo", '$.bar.yoo') "yoo", JSON_QUERY("foo", '$.bar.loo') AS l
            FROM "test" WHERE JSON_VALUE("foo", '$.bar.yoo') = 'www'
            HAVING JSON_QUERY("foo", '$.bar') = JSON_VALUE("hoo", '$.joo.moo')
            ORDER BY JSON_VALUE("foo", '$.bar.yoo') DESC
            SQL,
            $query->render(true)
        );
    }

    #[Test]
    public function autoAliasPrefixesColumnsWithTableAlias(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonContainsAll('params -> foo ->> bar', 'yoo');

        self::assertStringContainsString('JSON_TABLE("A"."PARAMS"', $q->render(true));
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
            SELECT * FROM "articles" "a"
                LEFT JOIN "ww_categories" "c" ON "a"."category_id" = "c"."id"
                WHERE EXISTS(SELECT 1 FROM JSON_TABLE("a"."params", '$.foo.bar[*]' COLUMNS ("VALUE" VARCHAR2(4000) PATH '$')) WHERE "value" = 'yoo')
            SQL,
            $q->render(true)
        );
    }

    #[Test]
    public function jsonNotContainsBuildsNegatedCondition(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->whereJsonNotContainsAll('params -> foo ->> bar', 'yoo');

        self::assertStringContainsString('WHERE NOT EXISTS(SELECT 1 FROM JSON_TABLE(', $q->render(true));
    }

    #[Test]
    public function jsonLengthBuildsLengthExpression(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonLength('params -> foo ->> bar', '>', 3);

        $sql = $q->render(true);

        self::assertStringContainsString("WHEN JSON_VALUE(\"A\".\"PARAMS\", '\$.foo.bar.type()') = 'object'", $sql);
        self::assertStringContainsString("'\$.foo.bar[*]'", $sql);
        self::assertStringEndsWith('END > 3', $sql);
    }

    #[Test]
    public function forUpdateRendersOracleLockClause(): void
    {
        $q = $this->instance->select('id')->from('foo')->forUpdate('SKIP LOCKED');

        self::assertSqlEquals('SELECT "id" FROM "foo" FOR UPDATE SKIP LOCKED', $q->render(true));
    }

    #[Test]
    public function forShareAppendsForShareSuffixWithOption(): void
    {
        $this->expectException(\LogicException::class);

        $this->instance->select('id')->from('foo')->forShare();
    }

    #[Test]
    public function cteAndInsertSelectRender(): void
    {
        $q = $this->instance->with(
            'cte',
            $this->instance->createSubQuery()->select('id')->from('foo')
        )->select('id')->from('cte');

        self::assertSqlEquals('WITH "cte" AS (SELECT "id" FROM "foo") SELECT "id" FROM "cte"', $q->render(true));

        $i = $this->instance->createSubQuery()->insert('bar')
            ->columns('id')
            ->values($this->instance->createSubQuery()->select('id')->from('foo'));

        self::assertSqlEquals('INSERT INTO "bar" ("id") (SELECT "id" FROM "foo")', $i->render(true));
    }

    #[Test]
    public function jsonCastSelector(): void
    {
        $query = $this->instance->select('a->n::int AS n')
            ->from('t')
            ->where('a->p::decimal', '>', 10)
            ->order("a->'x::y'::date");

        self::assertEquals(
            'SELECT JSON_VALUE("A", \'$.n\' RETURNING NUMBER) AS "N" FROM "T" WHERE JSON_VALUE("A", \'$.p\' RETURNING NUMBER) > 10 ORDER BY JSON_VALUE("A", \'$."x::y"\' RETURNING DATE)',
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
            'SELECT * FROM "T" WHERE JSON_EXISTS("A", \'$.b[1]\') AND NOT JSON_EXISTS("A", \'$.c\')',
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
            'UPDATE "T" SET "A" = JSON_TRANSFORM("A", SET \'$.b.c\' = \'{"x":1}\' FORMAT JSON), "A" = JSON_TRANSFORM("A", SET \'$.d\' = \'"str"\' FORMAT JSON), "A" = JSON_TRANSFORM("A", REMOVE \'$.e\')',
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
    public function jsonSetAsExpressionWithNesting(): void
    {
        $query = $this->instance->update('t');
        $query->set('foo', $query->jsonSet('bar->a', $query->jsonSet('baz->x', 1)))
            ->set('o', $query->jsonRemove('p->q'));

        self::assertEquals(
            'UPDATE "T" SET "FOO" = JSON_TRANSFORM("BAR", SET \'$.a\' = JSON_TRANSFORM("BAZ", SET \'$.x\' = \'1\' FORMAT JSON) FORMAT JSON), "O" = JSON_TRANSFORM("P", REMOVE \'$.q\')',
            $query->render(true)
        );
    }
}
