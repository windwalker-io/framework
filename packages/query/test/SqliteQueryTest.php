<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\SQLiteGrammar;
use Windwalker\Query\Query;

use function Windwalker\Query\qn;

/**
 * The SqliteQueryTest class.
 */
class SqliteQueryTest extends QueryTest
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
                'JSON_EXTRACT(`foo`, \'$.bar\')',
            ],
            [
                'foo->bar->1->>yoo',
                'JSON_EXTRACT(`foo`, \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar->1->>\'yoo\'',
                'JSON_EXTRACT(`foo`, \'$.bar[1].yoo\')',
            ],
            [
                'foo->bar->1->\'yoo\'',
                'JSON_EXTRACT(`foo`, \'$.bar[1].yoo\')',
            ],
            [
                'foo -> 2',
                'JSON_EXTRACT(`foo`, \'$[2]\')',
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
            SELECT JSON_EXTRACT(`foo`, '$.bar.yoo') AS `yoo`, JSON_EXTRACT(`foo`, '$.bar.loo') AS l
            FROM `test`
            WHERE JSON_EXTRACT(`foo`, '$.bar.yoo') = 'www'
            HAVING JSON_EXTRACT(`foo`, '$.bar') = JSON_EXTRACT(`hoo`, '$.joo.moo')
            ORDER BY JSON_EXTRACT(`foo`, '$.bar.yoo') DESC
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
              AND JSON_EXTRACT(`a`.`params`, '$.foo.bar') = 'yoo'
              AND `a`.`state` = 1
              AND `c`.`state` = 1
            SQL,
            $q->render(true)
        );
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new SQLiteGrammar();
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
            'SELECT CAST(JSON_EXTRACT(`a`, \'$.n\') AS INTEGER) AS `n` FROM `t` WHERE CAST(JSON_EXTRACT(`a`, \'$.p\') AS REAL) > 10 ORDER BY DATE(JSON_EXTRACT(`a`, \'$."x::y"\'))',
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
            'SELECT * FROM `t` WHERE JSON_TYPE(`a`, \'$.b[1]\') IS NOT NULL AND JSON_TYPE(`a`, \'$.c\') IS NULL',
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
            'UPDATE `t` SET `a` = JSON_SET(`a`, \'$.b.c\', JSON(\'{"x":1}\')), `a` = JSON_SET(`a`, \'$.d\', JSON(\'"str"\')), `a` = JSON_REMOVE(`a`, \'$.e\')',
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
            'UPDATE `t` SET `foo` = JSON_SET(`bar`, \'$.a\', JSON_SET(`baz`, \'$.x\', JSON(\'1\'))), `o` = JSON_REMOVE(`p`, \'$.q\')',
            $query->render(true)
        );
    }

    #[Test]
    public function unionCombinesQueries(): void
    {
        $q = self::createQuery();

        $q->union(
            self::createQuery()
                ->select('*')
                ->from('foo')
                ->where('id', '>', 12)
        );

        $q->union(
            self::createQuery()
                ->select('*')
                ->from('bar')
                ->where('id', '<', 50)
        );

        $q->group('id')->order('id', 'DESC');

        self::assertSqlEquals(
            'SELECT * FROM `foo` WHERE `id` > 12 UNION SELECT * FROM `bar` WHERE `id` < 50 GROUP BY `id` ORDER BY `id` DESC',
            $q
        );
    }

    #[Test]
    public function unionAcceptsClosure(): void
    {
        $q = self::createQuery();

        $q->union(fn(Query $q) => $q->select('*')->from('foo')->where('id', '>', 12));
        $q->unionAll(function (Query $q) {
            $q->select('*')->from('bar')->where('id', '<', 50);
        });

        self::assertSqlEquals(
            'SELECT * FROM `foo` WHERE `id` > 12 UNION SELECT * FROM `bar` WHERE `id` < 50',
            $q
        );
    }

    #[Test]
    public function insertBuildsInsertStatement(): void
    {
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
            'INSERT INTO `foo` (`id`, `title`) SELECT `id`, `title` FROM `articles` UNION SELECT `id`, `title` FROM `categories`',
            $q
        );
    }

    #[Test]
    public function recursiveCteWithUnionAll(): void
    {
        $q = self::createQuery()
            ->withRecursive(
                'cte',
                fn(Query $s) => $s->select('1 AS n')
                    ->unionAll(fn(Query $u) => $u->select('n + 1')->from('cte')->where('n', '<', 3))
            )
            ->select('n')
            ->from('cte');

        self::assertSqlEquals(
            'WITH RECURSIVE `cte` AS (SELECT `1` AS `n` UNION ALL SELECT `n + 1` FROM `cte` WHERE `n` < 3) SELECT `n` FROM `cte`',
            $q
        );
    }
}
