<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;

use function Windwalker\Query\qn;

/**
 * The PostgresqlQueryTest class.
 */
class PostgreSQLQueryTest extends QueryTest implements QueryJsonTestInterface
{
    /**
     * testParseJsonSelector
     *
     * @param  string  $selector
     * @param  string  $expected
     *
     * @return  void
     */
    #[DataProvider('parseJsonSelectorProvider')]
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
                '"foo"::jsonb->>\'bar\'',
            ],
            [
                'foo->bar->1->>yoo',
                '"foo"::jsonb->\'bar\'->1->>\'yoo\'',
            ],
            [
                'foo->bar->1->>\'yoo\'',
                '"foo"::jsonb->\'bar\'->1->>\'yoo\'',
            ],
            [
                'foo->bar->1->\'yoo\'',
                '"foo"::jsonb->\'bar\'->1->\'yoo\'',
            ],
            [
                'foo->2',
                '"foo"::jsonb->2',
            ],
        ];
    }

    #[Test]
    public function jsonQuoteWrapsJsonExpression(): void
    {
        $query = $this->instance->select('foo->bar ->> yoo AS yoo')
            ->selectRaw('%n AS l', 'foo->bar->loo')
            ->from('test')
            ->where('foo->bar ->> yoo', 'www')
            ->having('foo->bar', '=', qn('hoo->joo ->> moo'))
            ->order('foo->bar ->> yoo', 'DESC');

        self::assertSqlEquals(
            <<<SQL
            SELECT "foo"::jsonb->'bar'->>'yoo' AS "yoo", "foo"::jsonb->'bar'->'loo' AS l
            FROM "test"
            WHERE "foo"::jsonb->'bar'->>'yoo' = 'www'
            HAVING "foo"::jsonb->'bar' = "hoo"::jsonb->'joo'->>'moo'
            ORDER BY "foo"::jsonb->'bar'->>'yoo' DESC
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
            ->whereJsonContainsAll('params->foo ->> bar', 'yoo');

        self::assertSqlEquals(
            <<<SQL
            SELECT *
            FROM "articles" AS "a"
                     LEFT JOIN "ww_categories" AS "c" ON "a"."category_id" = "c"."id"
            WHERE ("a"."params"::jsonb->'foo'->'bar' @> '[\"yoo\"]')
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
            ->whereJsonNotContainsAll('params->foo ->> bar', 'yoo');

        self::assertSqlEquals(
            <<<SQL
            SELECT *
            FROM "articles" AS "a"
                     LEFT JOIN "ww_categories" AS "c" ON "a"."category_id" = "c"."id"
            WHERE NOT ("a"."params"::jsonb->'foo'->'bar' @> '[\"yoo\"]')
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
            ->whereJsonLength('params->foo ->> bar', '>', 3);

        self::assertSqlEquals(
            <<<SQL
            SELECT *
            FROM "articles" AS "a"
                     LEFT JOIN "ww_categories" AS "c" ON "a"."category_id" = "c"."id"
            WHERE CASE
                WHEN jsonb_typeof("a"."params"::jsonb->'foo'->'bar') = 'object'
                    THEN array_length(ARRAY (SELECT * FROM jsonb_object_keys("a"."params"::jsonb->'foo'->'bar')), 1)
                WHEN jsonb_typeof("a"."params"::jsonb->'foo'->'bar') = 'array'
                    THEN jsonb_array_length("a"."params"::jsonb->'foo'->'bar')
                ELSE 0
            END > 3
            SQL,
            $q->render(true)
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

    protected static function randomOrderSql(): string
    {
        return 'RANDOM()';
    }

    #[Test]
    public function jsonCastSelector(): void
    {
        $query = $this->instance->select('a->n::int AS n')
            ->from('t')
            ->where('a->p::decimal', '>', 10)
            ->order("a->'x::y'::date");

        self::assertEquals(
            'SELECT ("a"::jsonb->>\'n\')::bigint AS "n" FROM "t" WHERE ("a"::jsonb->>\'p\')::numeric > 10 ORDER BY ("a"::jsonb->>\'x::y\')::date',
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
            'SELECT * FROM "t" WHERE ("a"::jsonb #> CAST(\'{"b","1"}\' AS text[])) IS NOT NULL AND ("a"::jsonb #> CAST(\'{"c"}\' AS text[])) IS NULL',
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
            'UPDATE "t" SET "a" = jsonb_set("a"::jsonb, CAST(\'{"b","c"}\' AS text[]), CAST(\'{"x":1}\' AS jsonb)), "a" = jsonb_set("a"::jsonb, CAST(\'{"d"}\' AS text[]), CAST(\'"str"\' AS jsonb)), "a" = "a"::jsonb #- CAST(\'{"e"}\' AS text[])',
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
            'UPDATE "t" SET "foo" = jsonb_set("bar"::jsonb, CAST(\'{"a"}\' AS text[]), jsonb_set("baz"::jsonb, CAST(\'{"x"}\' AS text[]), CAST(\'1\' AS jsonb))), "o" = "p"::jsonb #- CAST(\'{"q"}\' AS text[])',
            $query->render(true)
        );
    }
}
