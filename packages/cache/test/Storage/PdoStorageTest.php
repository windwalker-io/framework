<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\GroupedStorageInterface;
use Windwalker\Cache\Storage\PdoStorage;
use Windwalker\Cache\Storage\PrunableStorageInterface;

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

    private function tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name");
        $stmt->execute([':name' => $table]);

        return $stmt->fetchColumn() !== false;
    }
}

