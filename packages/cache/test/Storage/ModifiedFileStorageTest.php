<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Windwalker\Cache\Storage\ModifiedFileStorage;

class ModifiedFileStorageTest extends TestCase
{
    #[Test]
    public function newerListenFilesInvalidateCachedEntries(): void
    {
        $root = sys_get_temp_dir() . '/windwalker-cache-modified-' . bin2hex(random_bytes(8));
        $listenFile = $root . '/source.php';
        $storage = null;

        try {
            self::assertTrue(mkdir($root));
            self::assertSame(0, file_put_contents($listenFile, ''));

            $storage = new ModifiedFileStorage($root, [$listenFile], ['lock' => false]);
            $storage->save('key', 'value');

            $cacheFile = $storage->fetchStreamUri('key');
            touch($cacheFile, 200);
            touch($listenFile, 100);
            clearstatcache(true);

            self::assertTrue($storage->has('key'));

            touch($listenFile, 300);
            clearstatcache(true);

            self::assertFalse($storage->has('key'));

            $storage->setListenFiles([new SplFileInfo($listenFile)]);
            self::assertTrue($storage->isModified('key'));
        } finally {
            $storage?->clear();

            if (is_file($listenFile)) {
                unlink($listenFile);
            }

            if (is_dir($root)) {
                rmdir($root);
            }
        }
    }
}
