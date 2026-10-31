<?php

declare(strict_types=1);

namespace Windwalker\Database\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\Pdo\PdoDriver;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\Database\Platform\PostgreSQLPlatform;
use Windwalker\Database\Platform\SQLitePlatform;
use Windwalker\Database\Platform\SQLServerPlatform;
use Windwalker\Query\Grammar\MySQLGrammar;

class DatabaseFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('driverNamesProvider')]
    public function extractDriverNameSeparatesDriverAndPlatform(string $name, array $expected): void
    {
        self::assertSame($expected, DatabaseFactory::extractDriverName($name));
    }

    public static function driverNamesProvider(): array
    {
        return [
            'with platform suffix' => ['pdo_mysql', ['pdo', 'mysql']],
            'without platform suffix' => ['sqlite', ['sqlite', 'sqlite']]
        ];
    }

    #[Test]
    #[DataProvider('driverShortNamesProvider')]
    public function getDriverShortNameNormalizesDriverAliases(string $name, string $expected): void
    {
        self::assertSame($expected, DatabaseFactory::getDriverShortName($name));
    }

    public static function driverShortNamesProvider(): array
    {
        return [
            'postgresql platform' => [DbPlatform::PostgreSQL->name, 'pgsql'],
            'sql server platform' => [DbPlatform::SQLServer->name, 'sqlsrv'],
            'pdo driver' => ['PDO', 'pdo']
        ];
    }

    #[Test]
    #[DataProvider('platformNamesProvider')]
    public function getPlatformNameNormalizesAliases(string $name, string $expected): void
    {
        self::assertSame($expected, DatabaseFactory::getPlatformName($name));
    }

    public static function platformNamesProvider(): array
    {
        return [
            'mysql driver' => ['mysqli', DbPlatform::MySQL->name],
            'postgresql driver' => ['pgsql', DbPlatform::PostgreSQL->name],
            'sql server driver' => ['sqlsrv', DbPlatform::SQLServer->name],
            'sqlite platform' => [DbPlatform::SQLite->name, DbPlatform::SQLite->name],
            'custom platform' => ['custom', 'Custom']
        ];
    }

    #[Test]
    #[DataProvider('platformsProvider')]
    public function createPlatformSelectsPlatformImplementation(string $platform, string $expectedClass): void
    {
        $created = new DatabaseFactory()->createPlatform($platform);

        self::assertInstanceOf($expectedClass, $created);
    }

    public static function platformsProvider(): array
    {
        return [
            'mysql' => [DbPlatform::MySQL->name, MySQLPlatform::class],
            'postgresql' => [DbPlatform::PostgreSQL->name, PostgreSQLPlatform::class],
            'sql server' => [DbPlatform::SQLServer->name, SQLServerPlatform::class],
            'sqlite' => [DbPlatform::SQLite->name, SQLitePlatform::class]
        ];
    }

    #[Test]
    public function createDriverAppliesDriverAndPlatformOptions(): void
    {
        $options = ['dbname' => 'example'];
        $driver = new DatabaseFactory()->createDriver('pdo_sqlite', $options);

        self::assertInstanceOf(PdoDriver::class, $driver);
        self::assertSame('pdo_sqlite', $driver->getOptions()->driver);
        self::assertSame(DbPlatform::SQLite->name, $driver->getOptions()->platform);
        self::assertSame('example', $options['dbname']);
    }

    #[Test]
    public function createBuildsAdapterFromDriverName(): void
    {
        $adapter = new DatabaseFactory()->create('pdo_sqlite', ['dbname' => 'example']);

        self::assertInstanceOf(PdoDriver::class, $adapter->driver);
        self::assertInstanceOf(SQLitePlatform::class, $adapter->platform);
    }

    #[Test]
    public function createBuildsAdapterAroundExistingDriver(): void
    {
        $driver = $this->createStub(AbstractDriver::class);
        $driver->method('getPlatformName')->willReturn(DbPlatform::MySQL->name);

        $adapter = new DatabaseFactory()->create($driver, []);

        self::assertSame($driver, $adapter->driver);
        self::assertInstanceOf(MySQLPlatform::class, $adapter->platform);
    }

    #[Test]
    public function createGrammarUsesPlatformGrammar(): void
    {
        self::assertInstanceOf(
            MySQLGrammar::class,
            new DatabaseFactory()->createGrammar(DbPlatform::MySQL->name)
        );
    }
}
