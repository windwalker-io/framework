<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Redis;
use Windwalker\Cache\Storage\RedisStorage;

class RedisStorageUnitTest extends TestCase
{
    #[Before]
    public function requireRedisExtension(): void
    {
        if (!class_exists(Redis::class)) {
            self::markTestSkipped('The Redis extension is not loaded.');
        }
    }

    #[Test]
    public function groupedClearDeletesOnlyKeysFromTheSelectedGroup(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('keys')
            ->with('users:*')
            ->willReturn(['users:one']);
        $redis->expects($this->once())
            ->method('del')
            ->with('users:one');

        self::assertTrue((new RedisStorage($redis, 'users'))->clear());
    }

    #[Test]
    public function groupedClearWithNoKeysDoesNotDeleteAnything(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('keys')
            ->with('users:*')
            ->willReturn([]);
        $redis->expects($this->never())->method('del');

        self::assertTrue((new RedisStorage($redis, 'users'))->clear());
    }

    #[Test]
    public function getMultipleReturnsExistingValuesAndSkipsMissingKeys(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('mGet')
            ->with(['users:first', 'users:missing'])
            ->willReturn(['VALUE', false]);

        self::assertSame(
            ['first' => 'VALUE'],
            (new RedisStorage($redis, 'users'))->getMultiple(['first', 'missing'])
        );
    }

    #[Test]
    public function getMultipleDoesNotConnectForAnEmptyKeyList(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->never())->method('mGet');

        self::assertSame([], (new RedisStorage($redis))->getMultiple([]));
    }

    #[Test]
    public function saveReturnsFalseWhenRedisRejectsTheValue(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('set')
            ->with('users:entry', 'VALUE')
            ->willReturn(false);
        $redis->expects($this->never())->method('expire');

        self::assertFalse((new RedisStorage($redis, 'users'))->save('entry', 'VALUE', time() + 60));
    }

    #[Test]
    public function getMultipleReturnsAnEmptyArrayWhenRedisDoesNotReturnAList(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('mGet')
            ->willReturn(false);

        self::assertSame([], (new RedisStorage($redis))->getMultiple(['first']));
    }
}
