<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;

use function Windwalker\Query\qn;

/**
 * The MySQLQueryTest class.
 */
class MySQLQueryTest extends QueryTest
{
    protected static array $nameQuote = ['`', '`'];

    /**
     * testParseJsonSelector
     *
     * @param  string  $selector
     * @param  string  $expected
     *
     * @return  void
     *
     * @dataProvider parseJsonSelectorProvider
     */
    public function testParseJsonSelector(string $selector, string $expected)
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

    public function parseJsonSelectorProvider(): array
    {
        return [
            [
                'foo ->> bar',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar\'))',
            ],
            [
                'foo->bar[1]->>yoo',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar[1].yoo\'))',
            ],
            [
                'foo->bar[1]->>\'yoo\'',
                'JSON_UNQUOTE(JSON_EXTRACT(`foo`, \'$.bar[1].yoo\'))',
            ],
            [
                'foo->bar[1]->\'yoo\'',
                'JSON_EXTRACT(`foo`, \'$.bar[1].yoo\')',
            ],
        ];
    }

    public function testJsonQuote(): void
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

    public function testAutoAlias(): void
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

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new MySQLGrammar();
    }
}
