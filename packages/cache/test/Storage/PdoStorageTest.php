<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\PdoStorage;
use Windwalker\Cache\Storage\PrunableStorageInterface;
use Windwalker\Cache\Storage\TouchableStorageInterface;

class PdoStorageTest extends TestCase
{
    protected string $dbFile;

    protected ?PDO $pdo = null;

    protected PdoStorage $instance;

    protected PdoStorage $otherGroup;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite extension is required.');
        }

        $this->dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        @mkdir(dirname($this->dbFile), 0755, true);

        register_shutdown_function(
            function () {
                @unlink($this->dbFile);
            }
        );

        $this->pdo = new PDO('sqlite:' . $this->dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(
            <<<'SQL'
            CREATE TABLE cache_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                "key" VARCHAR(255) NOT NULL,
                "group" VARCHAR(255) NOT NULL DEFAULT '',
                payload TEXT,
                expired_at DOUBLE NULL
            )
            SQL
        );
        $this->pdo->exec('CREATE UNIQUE INDEX cache_items_key_group_unique ON cache_items ("key", "group")');

        $this->instance = new PdoStorage($this->pdo, 'flower', 'cache_items', pruneProbability: 0.0);
        $this->otherGroup = new PdoStorage($this->pdo, 'tree', 'cache_items', pruneProbability: 0.0);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        @unlink($this->dbFile);
    }

    public function testImplementsPrunableStorageInterface(): void
    {
        self::assertInstanceOf(PrunableStorageInterface::class, $this->instance);
    }

    public function testImplementsGroupedStorageInterface(): void
    {
        self::assertInstanceOf(GroupedStorageInterface::class, $this->instance);
    }

    public function testImplementsTouchableStorageInterface(): void
    {
        self::assertInstanceOf(TouchableStorageInterface::class, $this->instance);
    }

    /**
     * @see  PdoStorage::updateExpiration
     */
    public function testUpdateExpirationUpdatesExpiredAtAndReturnsTrue(): void
    {
        $this->instance->save('foo', 'FOO', time() + 60);

        $newExpiration = time() + 3600;

        self::assertTrue($this->instance->updateExpiration('foo', $newExpiration));
        self::assertSame($newExpiration, $this->getExpiredAt('foo', 'flower'));
        self::assertSame('FOO', $this->instance->get('foo'));
    }

    /**
     * @see  PdoStorage::updateExpiration — returns false when the key does not exist
     */
    public function testUpdateExpirationReturnsFalseWhenKeyNotFound(): void
    {
        self::assertFalse($this->instance->updateExpiration('missing', time() + 60));
    }

    /**
     * @see  PdoStorage::updateExpiration — scoped to the storage's own group
     */
    public function testUpdateExpirationIsScopedToGroup(): void
    {
        $this->instance->save('same-key', 'FLOWER', time() + 60);
        $this->otherGroup->save('same-key', 'TREE', time() + 60);

        $newExpiration = time() + 3600;

        self::assertTrue($this->instance->updateExpiration('same-key', $newExpiration));
        self::assertSame($newExpiration, $this->getExpiredAt('same-key', 'flower'));
        self::assertNotSame($newExpiration, $this->getExpiredAt('same-key', 'tree'));
    }

    /**
     * @see  PdoStorage::updateExpiration — an expired timestamp still updates the row but item is
     *       no longer readable via get()/has()
     */
    public function testUpdateExpirationToPastMakesItemUnreadable(): void
    {
        $this->instance->save('foo', 'FOO', time() + 60);

        self::assertTrue($this->instance->updateExpiration('foo', time() - 10));

        self::assertFalse($this->instance->has('foo'));
        self::assertNull($this->instance->get('foo'));
    }

    public function testWithGroupCreatesScopedClone(): void
    {
        $flower = $this->instance->withGroup('flower');
        $tree = $this->instance->withGroup('tree');

        self::assertNotSame($flower, $tree);
        self::assertSame('flower', $this->instance->group);
        self::assertSame('flower', $flower->group);
        self::assertSame('tree', $tree->group);

        $flower->save('same-key', 'FLOWER', time() + 60);
        $tree->save('same-key', 'TREE', time() + 60);

        self::assertSame('FLOWER', $flower->get('same-key'));
        self::assertSame('TREE', $tree->get('same-key'));
    }

    public function testPruneRemovesOnlyExpiredEntriesInCurrentGroup(): void
    {
        $this->instance->save('expired', 'Sakura', time() - 10);
        $this->instance->save('active', 'Maple', time() + 60);
        $this->otherGroup->save('expired', 'Pine', time() - 10);

        self::assertSame(1, $this->instance->prune());
        self::assertSame(0, $this->countRows('cache_items', 'expired', 'flower'));
        self::assertSame(1, $this->countRows('cache_items', 'active', 'flower'));
        self::assertSame(1, $this->countRows('cache_items', 'expired', 'tree'));
        self::assertSame(0, $this->instance->prune());
    }

    public function testSaveCanAutoPruneExpiredEntries(): void
    {
        $storage = new PdoStorage($this->pdo, 'flower', 'cache_items', pruneProbability: 1.0);

        $storage->save('expired', 'Sakura', time() - 10);
        self::assertSame(0, $this->countRows('cache_items', 'expired', 'flower'));

        $storage->save('fresh', 'Maple', time() + 60);

        self::assertSame(0, $this->countRows('cache_items', 'expired', 'flower'));
        self::assertSame(1, $this->countRows('cache_items', 'fresh', 'flower'));
    }

    #[Test]
    public function clearOnlyRemovesEntriesFromTheCurrentGroup(): void
    {
        $this->instance->save('flower-key', 'FLOWER');
        $this->otherGroup->save('tree-key', 'TREE');

        self::assertTrue($this->instance->clear());
        self::assertNull($this->instance->get('flower-key'));
        self::assertSame('TREE', $this->otherGroup->get('tree-key'));
    }

    #[Test]
    public function removeReturnsSuccessForExistingAndMissingKeys(): void
    {
        $this->instance->save('existing', 'VALUE');

        self::assertTrue($this->instance->remove('existing'));
        self::assertFalse($this->instance->has('existing'));
        self::assertTrue($this->instance->remove('missing'));
    }

    #[Test]
    public function pruneAllRemovesExpiredEntriesAcrossGroups(): void
    {
        $this->instance->save('flower-expired', 'STALE', time() - 10);
        $this->instance->save('flower-active', 'FRESH', time() + 60);
        $this->otherGroup->save('tree-expired', 'STALE', time() - 10);

        self::assertSame(2, $this->instance->pruneAll());
        self::assertSame(1, $this->countRows('cache_items', 'flower-active', 'flower'));
        self::assertSame(0, $this->countRows('cache_items', 'flower-expired', 'flower'));
        self::assertSame(0, $this->countRows('cache_items', 'tree-expired', 'tree'));
    }

    #[Test]
    public function saveUsesDriverSpecificUpsertStatements(): void
    {
        $cases = [
            'mysql' => 'ON DUPLICATE KEY UPDATE',
            'pgsql' => 'ON CONFLICT',
            'sqlsrv' => 'MERGE INTO',
        ];

        foreach ($cases as $driver => $expectedSql) {
            $statement = $this->createMock(\PDOStatement::class);
            $statement->expects($this->once())
                ->method('execute')
                ->with($this->callback(static function (array $bindings) use ($driver): bool {
                    if ($driver === 'sqlsrv') {
                        return array_key_exists(':key1', $bindings)
                            && array_key_exists(':key2', $bindings)
                            && array_key_exists(':grp1', $bindings)
                            && array_key_exists(':grp2', $bindings);
                    }

                    return array_key_exists(':key', $bindings)
                        && array_key_exists(':grp', $bindings)
                        && array_key_exists(':payload', $bindings)
                        && array_key_exists(':expiredAt', $bindings);
                }))
                ->willReturn(true);

            $pdo = $this->createMock(PDO::class);
            $pdo->expects($this->atLeastOnce())
                ->method('getAttribute')
                ->with(PDO::ATTR_DRIVER_NAME)
                ->willReturn($driver);
            $pdo->expects($this->once())
                ->method('prepare')
                ->with($this->stringContains($expectedSql))
                ->willReturn($statement);

            self::assertTrue((new PdoStorage($pdo, 'flower', 'cache_items', pruneProbability: 0.0))->save('key', 'VALUE'));
        }
    }

    #[Test]
    public function saveUsesUpdateThenInsertForDriversWithoutUpsertSyntax(): void
    {
        $update = $this->createMock(\PDOStatement::class);
        $update->expects($this->once())
            ->method('execute')
            ->with(self::callback(static fn(array $bindings): bool => $bindings[':payload'] === 'VALUE'))
            ->willReturn(true);
        $update->expects($this->once())->method('rowCount')->willReturn(0);

        $insert = $this->createMock(\PDOStatement::class);
        $insert->expects($this->once())
            ->method('execute')
            ->with(self::callback(static fn(array $bindings): bool => $bindings[':key'] === 'key'))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('other');
        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($update, $insert);

        self::assertTrue((new PdoStorage($pdo, 'flower', 'cache_items', pruneProbability: 0.0))->save('key', 'VALUE'));
    }

    #[Test]
    public function saveRetriesAnUpdateWhenFallbackInsertLosesAConcurrentRace(): void
    {
        $update = $this->createMock(\PDOStatement::class);
        $update->expects($this->exactly(2))
            ->method('execute')
            ->with(self::callback(static fn(array $bindings): bool => $bindings[':key'] === 'key'))
            ->willReturn(true);
        $update->expects($this->once())->method('rowCount')->willReturn(0);

        $insert = $this->createMock(\PDOStatement::class);
        $insert->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('Duplicate key from concurrent insert.'));

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('other');
        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($update, $insert);

        self::assertTrue((new PdoStorage($pdo, 'flower', 'cache_items', pruneProbability: 0.0))->save('key', 'VALUE'));
    }

    #[Test]
    public function createTableCanBeCalledMoreThanOnce(): void
    {
        PdoStorage::createTable($this->pdo, 'repeat_cache_items');
        PdoStorage::createTable($this->pdo, 'repeat_cache_items');

        self::assertTrue($this->tableExists($this->pdo, 'repeat_cache_items'));
    }

    #[Test]
    #[DataProvider('createTableSchemaProvider')]
    public function createTableUsesTheDriverSpecificSchema(
        string $driver,
        string $expectedIdDefinition,
        string $expectedKeyDefinition
    ): void {
        $executedSql = [];
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn($driver);
        $pdo->expects($this->exactly(3))
            ->method('exec')
            ->willReturnCallback(
                static function (string $sql) use (&$executedSql): int {
                    $executedSql[] = $sql;

                    return 0;
                }
            );

        PdoStorage::createTable($pdo);

        self::assertStringContainsString($expectedIdDefinition, $executedSql[0]);
        self::assertStringContainsString($expectedKeyDefinition, $executedSql[0]);
        self::assertStringContainsString('cache_items_key_group_unique', $executedSql[1]);
        self::assertStringContainsString('cache_items_expired_at_index', $executedSql[2]);
    }

    /**
     * @return  array<string, array{string, string, string}>
     */
    public static function createTableSchemaProvider(): array
    {
        return [
            'sqlite' => ['sqlite', 'id INTEGER PRIMARY KEY AUTOINCREMENT', '"key" VARCHAR(255) NOT NULL'],
            'mysql' => ['mysql', 'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', '`key` VARCHAR(255) NOT NULL'],
            'postgresql' => ['pgsql', 'id BIGSERIAL PRIMARY KEY', '"key" VARCHAR(255) NOT NULL'],
            'sql server' => ['sqlsrv', "IF OBJECT_ID(N'cache_items', N'U') IS NULL", '[key] NVARCHAR(255) NOT NULL'],
            'fallback' => ['unknown', 'id BIGINT PRIMARY KEY', '"key" VARCHAR(255) NOT NULL']
        ];
    }

    #[Test]
    #[DataProvider('missingTableExceptionProvider')]
    public function missingTableDetectionUsesTheDriversErrorFormat(
        string $driver,
        string $message,
        ?array $errorInfo,
        int $code,
        bool $expected
    ): void {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn($driver);

        $exception = new PDOException($message, $code);
        $exception->errorInfo = $errorInfo;

        self::assertSame($expected, (new InspectablePdoStorage($pdo))->detectsMissingTable($exception));
    }

    /**
     * @return  array<string, array{string, string, ?array, int, bool}>
     */
    public static function missingTableExceptionProvider(): array
    {
        return [
            'postgresql state' => ['pgsql', 'relation missing', ['42P01', 0], 0, true],
            'sqlite message' => ['sqlite', 'no such table: cache_items', ['HY000', 1], 0, true],
            'oracle code' => ['oci', 'table missing', null, 942, true],
            'sql server code' => ['sqlsrv', 'table missing', null, 208, true],
            'mysql code' => ['mysql', 'table missing', ['42S02', 1146], 0, true],
            'unrecognized driver' => ['other', 'table missing', null, 0, false]
        ];
    }

    public function testAutoCreateTable(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $pdo = null;

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $storage = new PdoStorage($pdo, 'flower', 'auto_cache_items', pruneProbability: 0.0);

            self::assertTrue($storage->save('hello', 'world', time() + 60));
            self::assertSame('world', $storage->get('hello'));
        } finally {
            $pdo = null;
            @unlink($dbFile);
        }
    }

    public function testTableIsCreatedLazilyOnFirstSaveFailure(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $pdo = null;

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $storage = new PdoStorage($pdo, 'flower', 'lazy_cache_items', pruneProbability: 0.0, autoCreateTable: true);

            self::assertFalse($this->tableExists($pdo, 'lazy_cache_items'));

            self::assertTrue($storage->save('hello', 'world', time() + 60));

            self::assertTrue($this->tableExists($pdo, 'lazy_cache_items'));
            self::assertSame('world', $storage->get('hello'));
        } finally {
            $pdo = null;
            @unlink($dbFile);
        }
    }

    public function testSaveThrowsWhenAutoCreateTableDisabled(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $pdo = null;

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $storage = new PdoStorage($pdo, 'flower', 'no_auto_create_items', pruneProbability: 0.0, autoCreateTable: false);

            $this->expectException(PDOException::class);
            $storage->save('hello', 'world', time() + 60);
        } finally {
            $pdo = null;
            @unlink($dbFile);
        }
    }

    public function testGetThrowsWhenTableMissing(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $pdo = null;

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $storage = new PdoStorage($pdo, 'flower', 'missing_items', pruneProbability: 0.0);

            $this->expectException(PDOException::class);
            $storage->get('hello');
        } finally {
            $pdo = null;
            @unlink($dbFile);
        }
    }

    public function testHasAndRemoveAreSilentWhenTableMissing(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $pdo = null;

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $storage = new PdoStorage($pdo, 'flower', 'missing_items', pruneProbability: 0.0);

            self::assertFalse($storage->has('hello'));
            self::assertFalse($storage->remove('hello'));
        } finally {
            $pdo = null;
            @unlink($dbFile);
        }
    }

    private function countRows(string $table, string $key, string $group): int
    {
        $stmt = $this->pdo->prepare(
            sprintf('SELECT COUNT(*) FROM "%s" WHERE "key" = :key AND "group" = :grp', $table)
        );
        $stmt->execute([':key' => $key, ':grp' => $group]);

        return (int) $stmt->fetchColumn();
    }

    private function getExpiredAt(string $key, string $group): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT expired_at FROM cache_items WHERE "key" = :key AND "group" = :grp'
        );
        $stmt->execute([':key' => $key, ':grp' => $group]);

        $value = $stmt->fetchColumn();

        return $value === null ? null : (int) $value;
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name");
        $stmt->execute([':name' => $table]);

        return $stmt->fetchColumn() !== false;
    }
}

final class InspectablePdoStorage extends PdoStorage
{
    public function detectsMissingTable(PDOException $exception): bool
    {
        return $this->isTableNotFoundException($exception);
    }
}
