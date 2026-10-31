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
    public function upsertCompilesMergeUsingDual(): void
    {
        [$sql, $query] = self::compileUpsert(['id' => 1, 'title' => 'A'], ['id'], ['title']);

        self::assertSame(
            'MERGE INTO "foo" USING DUAL ON ("id" = :key_id) '
            . 'WHEN NOT MATCHED THEN INSERT ("id","title") VALUES (:id,:title) '
            . 'WHEN MATCHED THEN UPDATE SET "title" = :update_set_title',
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
