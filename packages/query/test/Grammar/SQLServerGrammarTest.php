<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\SQLServerGrammar;

use function Windwalker\raw;

class SQLServerGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return SQLServerGrammar::class;
    }

    #[Test]
    public function upsert(): void
    {
        [$sql, $query] = self::compileUpsert(
            ['id' => 1, 'title' => 'A', 'created' => raw('GETDATE()')],
            ['id'],
            ['title', 'created'],
            'id'
        );

        self::assertSame(
            'MERGE INTO [foo] WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ([id] = :key_id) '
            . 'WHEN NOT MATCHED THEN INSERT ([id],[title],[created]) VALUES (:id,:title,GETDATE()) '
            . 'WHEN MATCHED THEN UPDATE SET [title] = :update_set_title,[created]=GETDATE() '
            . 'OUTPUT INSERTED.[id];',
            $sql
        );
        self::assertArrayHasKey('key_id', $query->getBounded());
        self::assertArrayHasKey('update_set_title', $query->getBounded());
    }

    #[Test]
    public function upsertRequiresKeyInData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::compileUpsert(['title' => 'A'], ['id'], ['title']);
    }

    #[Test]
    public function insertWrapsIdentityInsert(): void
    {
        $grammar = self::createGrammar();

        $query = $grammar->createQuery()->insert('foo')->columns('a', 'b')->values([1, 2]);

        self::assertStringNotContainsString('IDENTITY_INSERT', $query->render(true));

        $query = $grammar->createQuery()->insert('foo', true)->columns('id', 'a')->values([1, 2]);

        self::assertStringContainsString('SET IDENTITY_INSERT [foo] ON;', $query->render(true));
        self::assertStringContainsString('SET IDENTITY_INSERT [foo] OFF;', $query->render(true));

        $query = $grammar->createQuery()->insert('foo')->set('a', 1);

        self::assertStringContainsString('SET', $query->render(true));
    }

    #[Test]
    public function limitUsesTopOrRowNumber(): void
    {
        $grammar = self::createGrammar();

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
    public function localEscapeStripsControlCharacters(): void
    {
        self::assertSame('', SQLServerGrammar::localEscape(''));
        self::assertSame('123', SQLServerGrammar::localEscape('123'));
        self::assertSame("it''s", SQLServerGrammar::localEscape("it's\x01\x0b%00"));
    }

    #[Test]
    public function listQueriesFilterBySchema(): void
    {
        $grammar = self::createGrammar();

        self::assertStringContainsString('sysdatabases', $grammar->listDatabases()->render(true));
        self::assertStringContainsString('BASE TABLE', $grammar->listTables()->render(true));
        self::assertStringContainsString('INFORMATION_SCHEMA', $grammar->listTables()->render(true));
        self::assertStringContainsString("'app'", $grammar->listTables('app')->render(true));
        self::assertStringContainsString('VIEW', $grammar->listViews()->render(true));
        self::assertStringContainsString('INFORMATION_SCHEMA', $grammar->listViews()->render(true));
        self::assertStringContainsString("'app'", $grammar->listViews('app')->render(true));
    }

    #[Test]
    public function dropTableDropsForeignKeysFirst(): void
    {
        $grammar = self::createGrammar();

        $sql = (string) $grammar->dropTable('foo', true);

        self::assertStringContainsString('DROP CONSTRAINT', $sql);
        self::assertStringContainsString('DROP TABLE IF EXISTS [foo]', $sql);
        self::assertStringNotContainsString('IF EXISTS', (string) $grammar->dropTable('foo'));
    }

    #[Test]
    public function jsonSelectorUsesJsonValueOrJsonQuery(): void
    {
        $grammar = self::createGrammar();
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
