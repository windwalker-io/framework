<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Query;

class BaseGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return BaseGrammar::class;
    }

    #[Test]
    public function createResolvesGrammarClassByName(): void
    {
        self::assertInstanceOf(MySQLGrammar::class, AbstractGrammar::create('mysql'));
        self::assertInstanceOf(BaseGrammar::class, AbstractGrammar::create('unknown'));
    }

    #[Test]
    public function compileValidatesTypeAndPrefixesSql(): void
    {
        $grammar = self::createGrammar();
        $query = $grammar->createQuery()->sql('SELECT 1');

        self::assertSame('SELECT 1', $grammar->compile('custom', $query));

        $query->prefix('/* hint */');

        self::assertSame('/* hint */ SELECT 1', $grammar->compile('custom', $query));

        try {
            $grammar->compile('', $query);
            self::fail('Empty type should throw');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(BadMethodCallException::class);

        $grammar->compile('nothing', $query);
    }

    #[Test]
    public function listsReturnEmptyQueries(): void
    {
        $grammar = self::createGrammar();

        self::assertInstanceOf(Query::class, $grammar->listDatabases());
        self::assertInstanceOf(Query::class, $grammar->listTables());
        self::assertInstanceOf(Query::class, $grammar->listViews());
    }
}
