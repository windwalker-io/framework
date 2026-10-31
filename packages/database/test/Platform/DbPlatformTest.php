<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Platform\DbPlatform;

class DbPlatformTest extends TestCase
{
    #[Test]
    #[DataProvider('aliasProvider')]
    public function wrapResolvesAliases(string $name, DbPlatform $expected): void
    {
        self::assertSame($expected, DbPlatform::wrap($name));
        self::assertSame($expected, DbPlatform::wrap(strtoupper($name)));
    }

    public static function aliasProvider(): array
    {
        return [
            ['mysql', DbPlatform::MySQL],
            ['mariadb', DbPlatform::MySQL],
            ['pgsql', DbPlatform::PostgreSQL],
            ['postgresql', DbPlatform::PostgreSQL],
            ['sqlite', DbPlatform::SQLite],
            ['sqlsrv', DbPlatform::SQLServer],
            ['sqlserver', DbPlatform::SQLServer],
        ];
    }

    #[Test]
    public function wrapReturnsEnumAsIs(): void
    {
        self::assertSame(DbPlatform::SQLite, DbPlatform::wrap(DbPlatform::SQLite));
    }

    #[Test]
    public function wrapRejectsUnknownName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DbPlatform::wrap('oracle');
    }
}
