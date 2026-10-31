<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractUpsertTestCase;

class SQLiteWriterManagerTest extends AbstractUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static function createUpsertTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_upsert_items');
        self::$baseConn->exec(
            'CREATE TABLE ww_upsert_items '
            . '(id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL UNIQUE, state INTEGER)'
        );
    }

    #[Test]
    public function filterFieldsKeepsOnlyTableColumns(): void
    {
        self::assertSame(
            ['title' => 'A'],
            $this->writer->filterFields('ww_upsert_items', ['title' => 'A', 'nope' => 1])
        );
    }
}
