<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\OracleGrammar;

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
            'MERGE INTO "FOO" USING DUAL ON ("ID" = :key_id) '
            . 'WHEN NOT MATCHED THEN INSERT ("ID","TITLE") VALUES (:id,:title) '
            . 'WHEN MATCHED THEN UPDATE SET "TITLE" = :update_set_title',
            $sql
        );
        self::assertSame('A', $query->getBounded()['update_set_title']['value']);
    }

    #[Test]
    public function upsertThrowsWhenKeyMissingFromData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::compileUpsert(['title' => 'A'], ['id'], ['title']);
    }
}
