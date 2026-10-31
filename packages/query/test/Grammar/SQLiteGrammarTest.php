<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use LogicException;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\SQLiteGrammar;

class SQLiteGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return SQLiteGrammar::class;
    }

    #[Test]
    public function upsert(): void
    {
        [$sql] = self::compileUpsert(['id' => 1, 'title' => 'A'], ['id'], ['title'], 'id');

        self::assertSame(
            'INSERT INTO `foo` (`id`,`title`) VALUES (:id,:title) '
            . 'ON CONFLICT (`id`) DO UPDATE SET `title` = excluded.`title` RETURNING `id`',
            $sql
        );
    }

    #[Test]
    public function jsonContainsAndLengthAreUnsupported(): void
    {
        $grammar = self::createGrammar();
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
    public function localEscapeDoublesSingleQuotes(): void
    {
        self::assertSame("a''b", SQLiteGrammar::localEscape("a'b"));
    }

    #[Test]
    public function identifierCannotChangeSortDirection(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite is unavailable.');
        }

        $pdo = new PDO('sqlite::memory:');
        $name = SQLiteGrammar::quoteName('id` DESC -- ');
        $sql = 'SELECT 1 AS ' . $name . ' UNION ALL SELECT 2 AS ' . $name . ' ORDER BY ' . $name;

        self::assertSame([1, 2], $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN));
    }
}
