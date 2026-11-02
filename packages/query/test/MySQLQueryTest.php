<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;

use function Windwalker\Query\qn;

/**
 * The MySQLQueryTest class.
 */
class MySQLQueryTest extends QueryTest implements QueryJsonTestInterface
{
    protected static array $nameQuote = ['`', '`'];

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
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar\'))',
            ],
            [
                'foo->bar->1->>yoo',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar[1].yoo\'))',
            ],
            [
                'foo->bar->1->>\'yoo\'',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar[1].yoo\'))',
            ],
            [
                'foo->bar->1->\'yoo\'',
                'JSON_EXTRACT(`foo`, \'$.bar[1].yoo\')',
            ],
            [
                'foo -> 2',
                'JSON_EXTRACT(`foo`, \'$[2]\')',
            ],
            [
                'foo->\'a.b\'->>\'c d\'',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$."a.b"."c d"\'))',
            ],
            [
                'foo->\'a->b\'->c',
                'JSON_EXTRACT(`foo`, \'$."a->b".c\')',
            ],
            [
                'foo->1.5',
                'JSON_EXTRACT(`foo`, \'$."1.5"\')',
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
            SELECT JSON_UNQUOTE(JSON_EXTRACT(`foo`, '$.bar.yoo')) AS `yoo`, JSON_EXTRACT(`foo`, '$.bar.loo') AS l
            FROM `test`
            WHERE JSON_UNQUOTE(JSON_EXTRACT(`foo`, '$.bar.yoo')) = 'www'
            HAVING JSON_EXTRACT(`foo`, '$.bar') = JSON_UNQUOTE(JSON_EXTRACT(`hoo`, '$.joo.moo'))
            ORDER BY JSON_UNQUOTE(JSON_EXTRACT(`foo`, '$.bar.yoo')) DESC
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
            ->where('id', 123)
            ->where('params -> foo ->> bar', 'yoo')
            ->where('a.state', 1)
            ->where('c.state', 1);

        self::assertSqlEquals(
            <<<SQL
            SELECT *
            FROM `articles` AS `a`
                     LEFT JOIN `ww_categories` AS `c` ON `a`.`category_id` = `c`.`id`
            WHERE `a`.`id` = 123
              AND JSON_UNQUOTE(JSON_EXTRACT(`a`.`params`, '$.foo.bar')) = 'yoo'
              AND `a`.`state` = 1
              AND `c`.`state` = 1
            SQL,
            $q->render(true)
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
            SELECT *
            FROM `articles` AS `a`
                     LEFT JOIN `ww_categories` AS `c` ON `a`.`category_id` = `c`.`id`
            WHERE JSON_CONTAINS(`a`.`params`, '[\"yoo\"]', '$.foo.bar')
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
            SELECT *
            FROM `articles` AS `a`
                     LEFT JOIN `ww_categories` AS `c` ON `a`.`category_id` = `c`.`id`
            WHERE NOT JSON_CONTAINS(`a`.`params`, '[\"yoo\"]', '$.foo.bar')
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
            SELECT *
            FROM `articles` AS `a`
                     LEFT JOIN `ww_categories` AS `c` ON `a`.`category_id` = `c`.`id`
            WHERE IFNULL(JSON_LENGTH(`a`.`params`, '$.foo.bar'), 0) > 3
            SQL,
            $q->render(true)
        );
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new MySQLGrammar();
    }

    protected static function orderByFieldSql(string $column): string
    {
        return "COALESCE(NULLIF(FIELD($column, 'b', 'a'), 0), 3)";
    }

    #[Test]
    public function jsonCastSelector(): void
    {
        $query = $this->instance->select('a->n::int AS n')
            ->from('t')
            ->where('a->p::decimal', '>', 10)
            ->order("a->'x::y'::date");

        self::assertEquals(
            'SELECT CAST(JSON_UNQUOTE(JSON_EXTRACT(`a`, \'$.n\')) AS SIGNED) AS `n` FROM `t` WHERE CAST(JSON_UNQUOTE(JSON_EXTRACT(`a`, \'$.p\')) AS DECIMAL(65, 30)) > 10 ORDER BY CAST(JSON_UNQUOTE(JSON_EXTRACT(`a`, \'$."x::y"\')) AS DATE)',
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
            'SELECT * FROM `t` WHERE JSON_CONTAINS_PATH(`a`, \'one\', \'$.b[1]\') AND NOT JSON_CONTAINS_PATH(`a`, \'one\', \'$.c\')',
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
            'UPDATE `t` SET `a` = JSON_SET(`a`, \'$.b.c\', CAST(\'{"x":1}\' AS JSON)), `a` = JSON_SET(`a`, \'$.d\', CAST(\'"str"\' AS JSON)), `a` = JSON_REMOVE(`a`, \'$.e\')',
            $query->render(true)
        );
    }

    #[Test]
    public function jsonCastRejectsUnsupportedType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->jsonSelector('a->b::money');
    }

    #[Test]
    public function jsonCastMustBeLast(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->jsonSelector('a->b::int->c');
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
    public function jsonExpressionErrors(): void
    {
        foreach (["a->'b", '->b', 'a->b::'] as $expr) {
            try {
                $this->instance->jsonSelector($expr);
                self::fail('Expected exception for: ' . $expr);
            } catch (\InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    #[Test]
    public function jsonSetAsExpressionWithNesting(): void
    {
        $query = $this->instance->update('t');
        $query->set('foo', $query->jsonSet('bar->a', $query->jsonSet('baz->x', 1)))
            ->set('o', $query->jsonRemove('p->q'));

        self::assertEquals(
            'UPDATE `t` SET `foo` = JSON_SET(`bar`, \'$.a\', JSON_SET(`baz`, \'$.x\', CAST(\'1\' AS JSON))), `o` = JSON_REMOVE(`p`, \'$.q\')',
            $query->render(true)
        );
    }
}
