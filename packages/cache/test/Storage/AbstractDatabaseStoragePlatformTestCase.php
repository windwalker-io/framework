<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Cache\Storage\DatabaseStorage;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

abstract class AbstractDatabaseStoragePlatformTestCase extends AbstractDatabaseTestCase
{
    protected static function setupDatabase(): void {}

    #[Test]
    public function saveUpsertsOnRealDatabase(): void
    {
        $db = self::$db;
        $db->getTableManager('ww_cache_upsert_items')->drop(true);
        DatabaseStorage::createTable($db, 'ww_cache_upsert_items');
        $s = new DatabaseStorage($db, 'g', 'ww_cache_upsert_items', [], 0.0);

        self::assertTrue($s->save('k', 'v1', 0));
        self::assertTrue($s->save('k', 'v2', time() + 1000));
        self::assertTrue($s->save('k2', 'x', 0));
        self::assertSame('v2', $s->get('k'));
        self::assertSame('x', $s->get('k2'));
        self::assertSame(2, (int) $db->select('*')->from('ww_cache_upsert_items')->count());
        $db->getTableManager('ww_cache_upsert_items')->drop(true);
    }
}
