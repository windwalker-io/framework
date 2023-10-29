<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;

use function Windwalker\Query\qn;

/**
 * The PostgresqlQueryTest class.
 */
class PostgreSQLQueryTest extends QueryTest implements QueryJsonTestInterface
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * testParseJsonSelector
     *
     * @param  string  $selector
     * @param  string  $expected
     *
     * @return  void
     */
    #[DataProvider('parseJsonSelectorProvider')]
    public function testParseJsonSelector(string $selector, string $expected): void
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

    public function testJsonQuote(): void
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

    public function testJsonContains(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonContains('params->foo ->> bar', 'yoo');

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

    public function testJsonNotContains(): void
    {
        $q = $this->instance->select();
        $q->from('articles', 'a')
            ->leftJoin('ww_categories', 'c', 'a.category_id', 'c.id')
            ->whereJsonNotContains('params->foo ->> bar', 'yoo');

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

    public function testJsonLength(): void
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
