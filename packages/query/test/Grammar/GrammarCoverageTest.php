<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Grammar\SQLiteGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

use function Windwalker\raw;

class GrammarCoverageTest extends TestCase
{
    private static ?MockEscaper $conn = null;

    private static function grammar(string $class): AbstractGrammar
    {
        return (new $class())->setEscaper(new Escaper(self::$conn ??= new MockEscaper()));
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
        $grammar = self::grammar(MySQLGrammar::class);
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
        self::assertSame("a''b", SQLiteGrammar::localEscape("a'b"));
        self::assertSame('A B', (string) MySQLGrammar::build('A', 'B'));
        self::assertSame('a=1 b=x', MySQLGrammar::buildConfig(['a' => 1, 'b' => 'x', 'c' => '']));
        self::assertInstanceOf(Clause::class, MySQLGrammar::clause('()', ['a']));
    }

    #[Test]
    public function mysqlLocalEscapeEscapesSpecialCharacters(): void
    {
        self::assertSame("a\\'b\\\"c\\n\\\\", MySQLGrammar::localEscape("a'b\"c\n\\"));
    }

    #[Test]
    public function mysqlListViewsFiltersBySchema(): void
    {
        $grammar = self::grammar(MySQLGrammar::class);

        self::assertStringContainsString('(SELECT DATABASE())', $grammar->listViews()->render(true));
        self::assertStringContainsString("'app'", $grammar->listViews('app')->render(true));
    }

    #[Test]
    public function sqliteJsonContainsAndLengthAreUnsupported(): void
    {
        $grammar = self::grammar(SQLiteGrammar::class);
        $query = $grammar->createQuery();

        try {
            $grammar->compileJsonContains($query, 'data', ['a'], 'x');
            self::fail('Expected LogicException');
        } catch (LogicException) {
            self::assertTrue(true);
        }

        $this->expectException(LogicException::class);

        $grammar->compileJsonLength($query, 'data', ['a']);
    }

    #[Test]
    public function baseGrammarListsReturnEmptyQueries(): void
    {
        $grammar = self::grammar(BaseGrammar::class);

        self::assertInstanceOf(Query::class, $grammar->listDatabases());
        self::assertInstanceOf(Query::class, $grammar->listTables());
        self::assertInstanceOf(Query::class, $grammar->listViews());
    }

    #[Test]
    public function sqlServerInsertWrapsIdentityInsert(): void
    {
        $grammar = self::grammar(SQLServerGrammar::class);

        $query = $grammar->createQuery()->insert('foo')->columns('a', 'b')->values([1, 2]);

        self::assertStringNotContainsString('IDENTITY_INSERT', $query->render(true));

        $query = $grammar->createQuery()->insert('foo', true)->columns('id', 'a')->values([1, 2]);

        self::assertStringContainsString('SET IDENTITY_INSERT [foo] ON;', $query->render(true));
        self::assertStringContainsString('SET IDENTITY_INSERT [foo] OFF;', $query->render(true));

        $query = $grammar->createQuery()->insert('foo')->set('a', 1);

        self::assertStringContainsString('SET', $query->render(true));
    }

    #[Test]
    public function sqlServerLimitUsesTopOrRowNumber(): void
    {
        $grammar = self::grammar(SQLServerGrammar::class);

        $sql = $grammar->createQuery()->select('*')->from('foo')->limit(5)->render(true);
        self::assertStringContainsString('SELECT TOP 5', $sql);

        $sql = $grammar->createQuery()->selectRaw('DISTINCT *')->from('foo')->limit(5)->render(true);
        self::assertStringContainsString('SELECT DISTINCT TOP 5', $sql);

        $sql = $grammar->createQuery()->select('*')->from('foo')->limit(5)->offset(10)->render(true);
        self::assertStringContainsString('SELECT TOP 15', $sql);
        self::assertStringContainsString('ROW_NUMBER()', $sql);
        self::assertStringContainsString('RowNumber > 10', $sql);

        $sql = $grammar->createQuery()->select('*')->from('foo')->offset(3)->render(true);
        self::assertStringContainsString('RowNumber > 3', $sql);
    }

    #[Test]
    public function sqlServerLocalEscapeStripsControlCharacters(): void
    {
        self::assertSame('', SQLServerGrammar::localEscape(''));
        self::assertSame('123', SQLServerGrammar::localEscape('123'));
        self::assertSame("it''s", SQLServerGrammar::localEscape("it's\x01\x0b%00"));
    }

    #[Test]
    public function sqlServerListQueriesFilterBySchema(): void
    {
        $grammar = self::grammar(SQLServerGrammar::class);

        self::assertStringContainsString('sysdatabases', $grammar->listDatabases()->render(true));
        self::assertStringContainsString('BASE TABLE', $grammar->listTables()->render(true));
        self::assertStringContainsString('INFORMATION_SCHEMA', $grammar->listTables()->render(true));
        self::assertStringContainsString("'app'", $grammar->listTables('app')->render(true));
        self::assertStringContainsString('VIEW', $grammar->listViews()->render(true));
        self::assertStringContainsString('INFORMATION_SCHEMA', $grammar->listViews()->render(true));
        self::assertStringContainsString("'app'", $grammar->listViews('app')->render(true));
    }

    #[Test]
    public function sqlServerDropTableDropsForeignKeysFirst(): void
    {
        $grammar = self::grammar(SQLServerGrammar::class);

        $sql = (string) $grammar->dropTable('foo', true);

        self::assertStringContainsString('DROP CONSTRAINT', $sql);
        self::assertStringContainsString('DROP TABLE IF EXISTS [foo]', $sql);
        self::assertStringNotContainsString('IF EXISTS', (string) $grammar->dropTable('foo'));
    }

    #[Test]
    public function sqlServerJsonSelectorUsesJsonValueOrJsonQuery(): void
    {
        $grammar = self::grammar(SQLServerGrammar::class);
        $query = $grammar->createQuery();

        self::assertSame(
            "JSON_VALUE([data], '$.a.b')",
            preg_replace('/\s+/', ' ', (string) $grammar->compileJsonSelector($query, 'data', ['a', 'b'], true, true))
        );
        self::assertStringContainsString(
            'JSON_QUERY(',
            (string) $grammar->compileJsonSelector($query, 'data', ['a'], false, true)
        );
    }
}
