<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\OracleGrammar;
use Windwalker\Query\Query;

class OracleGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return OracleGrammar::class;
    }

    #[Test]
    public function embeddedQuoteDelimitersAreEscaped(): void
    {
        $grammar = static::grammarClass();

        $quoted = '"ID"" DESC -- "';

        self::assertSame($quoted, $grammar::quoteName('id" DESC -- '));
        self::assertSame('"USERS".' . $quoted, $grammar::quoteName('users.id" DESC -- '));
        self::assertSame('"ID" AS ' . $quoted, $grammar::quoteName('id AS id" DESC -- '));
        self::assertSame('*', $grammar::quoteName('*'));
        self::assertSame('"USERS".*', $grammar::quoteName('users.*'));
        self::assertSame('"A.B"', $grammar::quoteName('a.b', true));
    }

    #[Test]
    public function upsertCompilesMergeUsingDual(): void
    {
        [$sql, $query] = self::compileUpsert(['id' => 1, 'title' => 'A'], ['id'], ['title']);

        self::assertSame(
            'MERGE INTO "FOO" USING DUAL ON ("ID" = :p1) '
            . 'WHEN NOT MATCHED THEN INSERT ("ID","TITLE") VALUES (:p2,:p3) '
            . 'WHEN MATCHED THEN UPDATE SET "TITLE" = :p4',
            $sql
        );
        self::assertCount(4, $query->getBounded());
    }

    #[Test]
    public function upsertThrowsWhenKeyMissingFromData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::compileUpsert(['title' => 'A'], ['id'], ['title']);
    }

    #[Test]
    public function staticHelpersReturnOracleValues(): void
    {
        self::assertSame('1900-01-01 00:00:00', OracleGrammar::nullDate());
        self::assertSame('Y-m-d H:i:s', OracleGrammar::dateFormat());
        self::assertSame('Oracle', OracleGrammar::getName());
    }

    #[Test]
    public function multiRowInsertCompilesUnionAllFromDual(): void
    {
        $query = new Query(null, new OracleGrammar())
            ->insert('foo')
            ->columns('id', 'title')
            ->values([1, 'a'], [2, 'b']);

        $sql = (string) $query->render();

        self::assertStringContainsString('FROM DUAL UNION ALL SELECT', $sql);
        self::assertStringNotContainsString('VALUES', $sql);
    }

    #[Test]
    public function limitCompilesOffsetFetch(): void
    {
        $query = new Query(null, new OracleGrammar())
            ->select('id')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(10);

        self::assertStringEndsWith('OFFSET 10 ROWS FETCH NEXT 5 ROWS ONLY', (string) $query->render());
    }
}
