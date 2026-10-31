<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\PostgreSQLGrammar;

class PostgreSQLGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return PostgreSQLGrammar::class;
    }

    #[Test]
    public function upsert(): void
    {
        [$sql] = self::compileUpsert(['id' => 1, 'title' => 'A'], ['id'], ['title']);

        self::assertSame(
            'INSERT INTO "foo" ("id","title") VALUES (:id,:title) '
            . 'ON CONFLICT ("id") DO UPDATE SET "title" = EXCLUDED."title"',
            $sql
        );
    }

    #[Test]
    public function upsertWithReturning(): void
    {
        [$sql] = self::compileUpsert(['id' => 1], ['id'], ['id'], 'id');

        self::assertStringEndsWith('RETURNING "id"', $sql);
    }
}
