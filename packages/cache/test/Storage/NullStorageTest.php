<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\NullStorage;

class NullStorageTest extends TestCase
{
    #[Test]
    public function itAlwaysBehavesAsAnEmptyStorage(): void
    {
        $storage = new NullStorage;

        self::assertNull($storage->get('key'));
        self::assertFalse($storage->has('key'));
        self::assertTrue($storage->save('key', 'value'));
        self::assertTrue($storage->remove('key'));
        self::assertTrue($storage->clear());

        $grouped = $storage->withGroup('users');

        self::assertNotSame($storage, $grouped);
        self::assertFalse($grouped->has('key'));
    }
}
