<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Grammar\PostgreSQLGrammar;
use Windwalker\Query\Grammar\SQLiteGrammar;
use Windwalker\Query\Grammar\SQLServerGrammar;

use function Windwalker\raw;

class IdentifierQuotingTest extends TestCase
{
    /** @param  class-string<AbstractGrammar>  $grammar */
    #[DataProvider('grammarProvider')]
    public function testEmbeddedDelimiters(string $grammar, string $open, string $close): void
    {
        $name = 'id' . $close . ' DESC -- ';
        $quoted = $open . 'id' . $close . $close . ' DESC -- ' . $close;

        self::assertSame($quoted, $grammar::quoteName($name));
        self::assertSame($open . 'users' . $close . '.' . $quoted, $grammar::quoteName('users.' . $name));
        self::assertSame($open . 'id' . $close . ' AS ' . $quoted, $grammar::quoteName('id AS ' . $name));
        self::assertSame('*', $grammar::quoteName('*'));
        self::assertSame($open . 'users' . $close . '.*', $grammar::quoteName('users.*'));
        self::assertSame('COUNT(*)', $grammar::quoteName(raw('COUNT(*)')));
        self::assertSame($open . 'a.b' . $close, $grammar::quoteName('a.b', true));
    }

    public static function grammarProvider(): array
    {
        return [
            'base' => [AbstractGrammar::class, '"', '"'],
            'mysql' => [MySQLGrammar::class, '`', '`'],
            'postgresql' => [PostgreSQLGrammar::class, '"', '"'],
            'sqlite' => [SQLiteGrammar::class, '`', '`'],
            'sqlserver' => [SQLServerGrammar::class, '[', ']'],
        ];
    }

    public function testIdentifierCannotChangeSortDirection(): void
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


