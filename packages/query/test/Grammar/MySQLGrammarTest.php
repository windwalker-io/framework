<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Grammar\MySQLGrammar;

use function Windwalker\raw;

class MySQLGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return MySQLGrammar::class;
    }

    #[Test]
    public function upsert(): void
    {
        [$sql, $query] = self::compileUpsert(
            ['id' => 1, 'title' => 'A', 'created' => raw('NOW()')],
            ['id'],
            ['title', 'created'],
            'id'
        );

        self::assertSame(
            'INSERT INTO `foo` (`id`,`title`,`created`) VALUES (:id,:title,NOW()) '
            . 'ON DUPLICATE KEY UPDATE `title` = VALUES(`title`),`created` = VALUES(`created`),'
            . '`id` = LAST_INSERT_ID(`id`)',
            $sql
        );
        self::assertSame(['id', 'title'], array_keys($query->getBounded()));
    }

    #[Test]
    public function quoteNameMultipleHandlesRawClauseAndIterables(): void
    {
        self::assertSame('COUNT(*)', MySQLGrammar::quoteNameMultiple(raw('COUNT(*)')));
        self::assertSame(['`a`', '`b`.`c`'], MySQLGrammar::quoteNameMultiple(['a', 'b.c']));

        $clause = MySQLGrammar::quoteNameMultiple(new Clause('()', ['a', 'b'], ','));

        self::assertSame('(`a`,`b`)', (string) $clause);
    }

    #[Test]
    public function staticHelpersBuildClausesAndConfigs(): void
    {
        self::assertSame('1000-01-01 00:00:00', MySQLGrammar::nullDate());
        self::assertSame('Y-m-d H:i:s', MySQLGrammar::dateFormat());
        self::assertSame('MySQL', MySQLGrammar::getName());
        self::assertSame('A B', (string) MySQLGrammar::build('A', 'B'));
        self::assertSame('a=1 b=x', MySQLGrammar::buildConfig(['a' => 1, 'b' => 'x', 'c' => '']));
        self::assertInstanceOf(Clause::class, MySQLGrammar::clause('()', ['a']));
    }

    #[Test]
    public function localEscapeEscapesSpecialCharacters(): void
    {
        self::assertSame("a\\'b\\\"c\\n\\\\", MySQLGrammar::localEscape("a'b\"c\n\\"));
    }

    #[Test]
    public function listViewsFiltersBySchema(): void
    {
        $grammar = self::createGrammar();

        self::assertStringContainsString('(SELECT DATABASE())', $grammar->listViews()->render(true));
        self::assertStringContainsString("'app'", $grammar->listViews('app')->render(true));
    }
}
