<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Memcached;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\MemcachedStorage;

class MemcachedStorageUnitTest extends TestCase
{
    #[Before]
    public function requireMemcachedExtension(): void
    {
        if (!class_exists(Memcached::class)) {
            self::markTestSkipped('The Memcached extension is not loaded.');
        }
    }

    #[Test]
    public function getReturnsAValueOnlyWhenTheResultCodeIsSuccessful(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())->method('get')->with('key')->willReturn('VALUE');
        $driver->expects($this->once())->method('getResultCode')->willReturn(Memcached::RES_SUCCESS);

        self::assertSame('VALUE', new MemcachedStorage($driver)->get('key'));
    }

    #[Test]
    public function hasReturnsFalseForMissingKeys(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())->method('get')->with('missing');
        $driver->expects($this->once())->method('getResultCode')->willReturn(Memcached::RES_NOTFOUND);

        self::assertFalse(new MemcachedStorage($driver)->has('missing'));
    }

    #[Test]
    public function clearAndRemoveReportMemcachedResultCodes(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())->method('flush')->willReturn(true);
        $driver->expects($this->exactly(2))
            ->method('getResultCode')
            ->willReturnOnConsecutiveCalls(Memcached::RES_SUCCESS, Memcached::RES_SUCCESS);
        $driver->expects($this->once())->method('delete')->with('key')->willReturn(true);

        $storage = new MemcachedStorage($driver);

        self::assertTrue($storage->clear());
        self::assertTrue($storage->remove('key'));
    }

    #[Test]
    public function savePassesTheExpirationToMemcached(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())->method('set')->with('key', 'VALUE', 1234)->willReturn(true);
        $driver->expects($this->once())->method('getResultCode')->willReturn(Memcached::RES_SUCCESS);

        self::assertTrue(new MemcachedStorage($driver)->save('key', 'VALUE', 1234));
    }

    #[Test]
    public function getMultipleReturnsValuesOnlyForPresentKeys(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())
            ->method('getMulti')
            ->with(['present', 'missing'])
            ->willReturn(['present' => 'VALUE']);

        self::assertSame(['present' => 'VALUE'], new MemcachedStorage($driver)->getMultiple(['present', 'missing']));
    }

    #[Test]
    public function getMultipleReturnsEmptyForEmptyInputOrFailedRequest(): void
    {
        $driver = $this->createMock(Memcached::class);
        $driver->expects($this->once())
            ->method('getMulti')
            ->with(['key'])
            ->willReturn(false);

        self::assertSame([], new MemcachedStorage($driver)->getMultiple([]));
        self::assertSame([], new MemcachedStorage($driver)->getMultiple(['key']));
    }
}
