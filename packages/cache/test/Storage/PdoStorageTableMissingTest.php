<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Throwable;
use Windwalker\Cache\Storage\PdoStorage;

/**
 * Verifies the per-driver "table not found" detection against real databases.
 *
 * Each case is skipped when its DSN constant is undefined, the PDO extension is
 * missing, or the database cannot be reached.
 */
class PdoStorageTableMissingTest extends TestCase
{
    public static function driverProvider(): array
    {
        return [
            'sqlite' => ['sqlite', 'WINDWALKER_TEST_DB_DSN_SQLITE'],
            'mysql' => ['mysql', 'WINDWALKER_TEST_DB_DSN_MYSQL'],
            'pgsql' => ['pgsql', 'WINDWALKER_TEST_DB_DSN_POSTGRESQL'],
            'sqlsrv' => ['sqlsrv', 'WINDWALKER_TEST_DB_DSN_SQLSERVER'],
            'oci' => ['oci', 'WINDWALKER_TEST_DB_DSN_ORACLE'],
        ];
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function missingTableIsDetectedOnRealDatabase(string $driver, string $constant): void
    {
        $pdo = static::connect($driver, $constant);

        $storage = new PdoStorage($pdo, 'flower', 'ww_no_such_cache_table', pruneProbability: 0.0);
        $method = new ReflectionMethod($storage, 'isTableNotFoundException');

        $missing = static::catchError($pdo, 'SELECT * FROM ' . static::quote($driver, 'ww_no_such_cache_table'));

        self::assertTrue($method->invoke($storage, $missing));

        $syntax = static::catchError($pdo, 'SELEKT 1');

        self::assertFalse($method->invoke($storage, $syntax));
    }

    #[Test]
    public function oracleStorageRoundTripCreatesTableAndUpserts(): void
    {
        $pdo = static::connect('oci', 'WINDWALKER_TEST_DB_DSN_ORACLE');

        try {
            $pdo->exec('DROP TABLE "ww_cache_oci_test"');
        } catch (PDOException) {
        }

        foreach (['', 'tree'] as $group) {
            $storage = new PdoStorage($pdo, $group, 'ww_cache_oci_test', pruneProbability: 0.0);

            // First save auto-creates the missing table
            $big = str_repeat('é中A', 3000);
            self::assertTrue($storage->save('k', $big));
            self::assertSame($big, $storage->get('k'));
            self::assertTrue($storage->save('k', 'B'));
            self::assertSame('B', $storage->get('k'));
            self::assertTrue($storage->has('k'));
            self::assertTrue($storage->updateExpiration('k', time() - 10));
            self::assertNull($storage->get('k'));
            self::assertSame(1, $storage->prune());
            self::assertTrue($storage->save('k2', 'C'));
            self::assertTrue($storage->remove('k2'));
            self::assertNull($storage->get('k2'));
        }

        $pdo->exec('DROP TABLE "ww_cache_oci_test"');
    }

    private static function connect(string $driver, string $constant): PDO
    {
        $dsn = defined($constant) ? (string) constant($constant) : '';

        if ($dsn === '' || !in_array($driver, PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped("$constant and the pdo_$driver extension are required.");
        }

        parse_str(str_replace(';', '&', $dsn), $p);

        $host = $p['host'] ?? '127.0.0.1';
        $port = $p['port'] ?? null;
        $db = $p['dbname'] ?? '';

        $pdoDsn = match ($driver) {
            'sqlite' => 'sqlite::memory:',
            'mysql' => "mysql:host=$host;dbname=$db",
            'pgsql' => "pgsql:host=$host;dbname=$db",
            'sqlsrv' => "sqlsrv:Server=$host" . ($port ? ",$port" : '') . ";Database=$db;TrustServerCertificate=1",
            'oci' => "oci:dbname=//$host:" . ($port ?? 1521) . "/$db;charset=AL32UTF8",
        };

        try {
            return new PDO(
                $pdoDsn,
                $p['user'] ?? null,
                $p['password'] ?? null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Throwable $e) {
            self::markTestSkipped("Unable to connect to $driver: " . $e->getMessage());
        }
    }

    private static function catchError(PDO $pdo, string $sql): PDOException
    {
        try {
            $pdo->query($sql);
        } catch (PDOException $e) {
            return $e;
        }

        self::fail("Expected query to fail: $sql");
    }

    private static function quote(string $driver, string $name): string
    {
        return match ($driver) {
            'mysql' => "`$name`",
            'sqlsrv' => "[$name]",
            default => "\"$name\"",
        };
    }
}
