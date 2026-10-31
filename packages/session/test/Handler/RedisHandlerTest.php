<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Redis;
use Windwalker\Session\Handler\RedisHandler;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RedisHandlerTest extends TestCase
{
    #[Test]
    #[DataProvider('ttlProvider')]
    public function writeAndUpdateTimestampUseResolvedTtl(array $options, int $expected): void
    {
        $previous = ini_set('session.gc_maxlifetime', '180');

        try {
            $driver = $this->createMock(Redis::class);
            $driver->expects($this->once())->method('setEx')->with('ww_sess_id', $expected, 'data')->willReturn(true);
            $driver->expects($this->once())->method('expire')->with('ww_sess_id', $expected)->willReturn(true);
            $handler = new RedisHandler($driver, $options);

            self::assertTrue($handler->write('id', 'data'));
            self::assertTrue($handler->updateTimestamp('id', 'data'));
        } finally {
            ini_set('session.gc_maxlifetime', $previous);
        }
    }

    public static function ttlProvider(): iterable
    {
        yield 'ttl overrides lifetime' => [['ttl' => 30, 'gc_maxlifetime' => 300], 30];
        yield 'numeric string ttl' => [['ttl' => '45'], 45];
        yield 'lifetime fallback' => [['gc_maxlifetime' => 300], 300];
        yield 'null ttl fallback' => [['ttl' => null, 'gc_maxlifetime' => 300], 300];
        yield 'ini fallback' => [[], 180];
    }

    #[Test]
    public function setOptionChangesTtlAfterConstruction(): void
    {
        $driver = $this->createMock(Redis::class);
        $driver->expects($this->once())->method('setEx')->with('ww_sess_id', 45, 'data')->willReturn(true);
        $driver->expects($this->once())->method('expire')->with('ww_sess_id', 45)->willReturn(true);
        $handler = new RedisHandler($driver, ['ttl' => 30]);
        $handler->setOption('ttl', 45);

        self::assertTrue($handler->write('id', 'data'));
        self::assertTrue($handler->updateTimestamp('id', 'data'));
    }

    #[Test]
    #[DataProvider('invalidTtlProvider')]
    public function operationRejectsInvalidTtl(mixed $ttl, string $operation, string $option): void
    {
        $driver = $this->createMock(Redis::class);
        $driver->expects($this->never())->method('setEx');
        $driver->expects($this->never())->method('expire');
        $handler = new RedisHandler($driver, [$option => $ttl]);

        $this->expectException(InvalidArgumentException::class);

        $handler->$operation('id', 'data');
    }

    public static function invalidTtlProvider(): iterable
    {
        foreach ([0, -1, 'invalid', '1.5', true, 1.5] as $index => $ttl) {
            foreach (['write', 'updateTimestamp'] as $operation) {
                foreach (['ttl', 'gc_maxlifetime'] as $option) {
                    yield "$index:$operation:$option" => [$ttl, $operation, $option];
                }
            }
        }
    }

    #[Test]
    public function realRedisRoundTripsReadWriteAndDestroy(): void
    {
        $redis = $this->connectRealRedis();
        $handler = new RedisHandler($redis, ['prefix' => 'ww_test_sess_', 'ttl' => 60]);

        try {
            self::assertTrue(RedisHandler::isSupported());
            self::assertSame('', $handler->read('missing'));
            self::assertTrue($handler->write('abc', 'payload'));
            self::assertSame('payload', $handler->read('abc'));
            self::assertTrue($handler->updateTimestamp('abc', 'payload'));
            self::assertLessThanOrEqual(60, $redis->ttl('ww_test_sess_abc'));
            self::assertGreaterThan(0, $redis->ttl('ww_test_sess_abc'));
            self::assertSame(0, $handler->gc(1440));
            self::assertTrue($handler->destroy('abc'));
            self::assertSame('', $handler->read('abc'));
        } finally {
            $redis->del('ww_test_sess_abc');
            $redis->close();
        }
    }

    private function connectRealRedis(): Redis
    {
        $redis = new Redis();

        try {
            $connected = $redis->connect('127.0.0.1', 6379, 1.0);
        } catch (\RedisException) {
            $connected = false;
        }

        if (!$connected) {
            self::markTestSkipped('A running Redis server is required.');
        }

        return $redis;
    }

    #[Before]
    protected function requireRedisExtension(): void
    {
        if (!class_exists(Redis::class)) {
            self::markTestSkipped('The redis extension is required for the driver mock.');
        }
    }
}
