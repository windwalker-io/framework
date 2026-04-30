<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\DatabaseStorage;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\PrunableStorageInterface;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Exception\DatabaseQueryException;

class DatabaseStorageTest extends TestCase
{
    protected string $dbFile;

    protected DatabaseAdapter $db;

    protected DatabaseStorage $instance;

    protected DatabaseStorage $otherGroup;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite extension is required.');
        }

        $this->dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        mkdir(dirname($this->dbFile), 755);

        register_shutdown_function(
            function () {
                @unlink($this->dbFile);
            }
        );

        $this->db = new DatabaseFactory()->create(
            'pdo_sqlite',
            new DriverOptions(
                file: $this->dbFile
            )
        );

        $this->db->execute(
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
        $this->db->execute(
            'CREATE UNIQUE INDEX cache_items_key_group_unique ON cache_items ("key", "group")'
        );

        $this->instance = new DatabaseStorage($this->db, 'flower', 'cache_items', [], 0.0);
        $this->otherGroup = new DatabaseStorage($this->db, 'tree', 'cache_items', [], 0.0);
    }

    protected function tearDown(): void
    {
        $this->db->disconnect();
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
        self::assertSame(0, $this->countRows('expired', 'flower'));
        self::assertSame(1, $this->countRows('active', 'flower'));
        self::assertSame(1, $this->countRows('expired', 'tree'));
        self::assertSame(0, $this->instance->prune());
    }

    public function testSaveCanAutoPruneExpiredEntries(): void
    {
        $storage = new DatabaseStorage($this->db, 'flower', 'cache_items', [], 1.0);

        // Save an expired entry - it will be pruned after save
        $storage->save('expired', 'Sakura', time() - 10);
        self::assertSame(0, $this->countRows('expired', 'flower')); // Already pruned

        $storage->save('fresh', 'Maple', time() + 60);

        self::assertSame(0, $this->countRows('expired', 'flower'));
        self::assertSame(1, $this->countRows('fresh', 'flower'));
    }

    public function testAutoCreateTable(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $db = null;

        try {
            $db = new DatabaseFactory()->create(
                'pdo_sqlite',
                new DriverOptions(
                    file: $dbFile
                )
            );

            $storage = new DatabaseStorage($db, 'flower', 'auto_cache_items', [], 0.0);

            self::assertTrue($storage->save('hello', 'world', time() + 60));
            self::assertSame('world', $storage->get('hello'));
        } finally {
            $db?->disconnect();
            @unlink($dbFile);
        }
    }

    public function testTableIsCreatedLazilyOnFirstSaveFailure(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $db = null;

        try {
            $db = new DatabaseFactory()->create(
                'pdo_sqlite',
                new DriverOptions(
                    file: $dbFile
                )
            );

            $storage = new DatabaseStorage($db, 'flower', 'lazy_cache_items', [], 0.0, true);

            self::assertFalse($db->getTableManager('lazy_cache_items')->exists());

            self::assertTrue($storage->save('hello', 'world', time() + 60));

            self::assertTrue($db->getTableManager('lazy_cache_items')->exists());
            self::assertSame('world', $storage->get('hello'));
        } finally {
            $db?->disconnect();
            @unlink($dbFile);
        }
    }

    public function testSaveThrowsWhenAutoCreateTableDisabled(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $db = null;

        try {
            $db = new DatabaseFactory()->create(
                'pdo_sqlite',
                new DriverOptions(
                    file: $dbFile
                )
            );

            $storage = new DatabaseStorage($db, 'flower', 'no_auto_create_items', [], 0.0, false);

            $this->expectException(DatabaseQueryException::class);
            $storage->save('hello', 'world', time() + 60);
        } finally {
            $db?->disconnect();
            @unlink($dbFile);
        }
    }

    public function testGetThrowsWhenTableMissing(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $db = null;

        try {
            $db = new DatabaseFactory()->create(
                'pdo_sqlite',
                new DriverOptions(
                    file: $dbFile
                )
            );

            $storage = new DatabaseStorage($db, 'flower', 'missing_items', [], 0.0);

            $this->expectException(DatabaseQueryException::class);
            $storage->get('hello');
        } finally {
            $db?->disconnect();
            @unlink($dbFile);
        }
    }

    public function testHasAndRemoveAreSilentWhenTableMissing(): void
    {
        $dbFile = __DIR__ . '/../../tmp/' . bin2hex(random_bytes(8)) . '.sqlite';
        $db = null;

        try {
            $db = new DatabaseFactory()->create(
                'pdo_sqlite',
                new DriverOptions(
                    file: $dbFile
                )
            );

            $storage = new DatabaseStorage($db, 'flower', 'missing_items', [], 0.0);

            self::assertFalse($storage->has('hello'));
            self::assertFalse($storage->remove('hello'));
        } finally {
            $db?->disconnect();
            @unlink($dbFile);
        }
    }

    private function countRows(string $key, string $group): int
    {
        return (int) $this->db->execute(
            'SELECT COUNT(*) FROM cache_items WHERE "key" = ? AND "group" = ?',
            [$key, $group]
        )->result();
    }
}


