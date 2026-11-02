<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Serializer\PhpFileSerializer;
use Windwalker\Cache\Storage\PhpFileStorage;

class PhpFileStorageTest extends TestCase
{
    #[Test]
    public function cacheValuesAndFetchMetadataSurviveAcrossPoolInstances(): void
    {
        $root = sys_get_temp_dir() . '/windwalker-php-file-cache-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($root));

        $storage = new PhpFileStorage($root, ['lock' => false], 0.0);

        try {
            $pool = new CachePool($storage, new PhpFileSerializer());
            $item = CacheItem::create('php-file-entry')
                ->set('VALUE')
                ->expiresAfter(3600)
                ->setCtime(42);

            ob_start();

            try {
                self::assertTrue($pool->save($item));

                $restored = new CachePool($storage, new PhpFileSerializer())->getItem('php-file-entry');
            } finally {
                ob_end_clean();
            }

            self::assertTrue($restored->isHit());
            self::assertSame('VALUE', $restored->get());
            self::assertSame(42, $restored->ctime);
            self::assertEqualsWithDelta($item->realExpiry, $restored->realExpiry, 0.00001);
        } finally {
            $storage->clear();
            rmdir($root);
        }
    }
}
