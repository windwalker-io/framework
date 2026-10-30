<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\Exception\InvalidArgumentException;

class CacheItemTest extends TestCase
{
    #[Test]
    public function createdItemIsMissUntilAValueIsSet(): void
    {
        $item = CacheItem::create('article');

        self::assertFalse($item->isHit());
        self::assertNull($item->get());

        $item->set('value');

        self::assertTrue($item->isHit());
        self::assertSame('value', $item->get());
    }

    #[Test]
    public function nullCanBeStoredAsAHit(): void
    {
        $item = CacheItem::create('nullable')->set(null);

        self::assertTrue($item->isHit());
        self::assertNull($item->get());
    }

    #[Test]
    public function createCanInitializeAHit(): void
    {
        $item = CacheItem::create('initial', 'value');

        self::assertTrue($item->isHit());
        self::assertSame('value', $item->get());
    }

    #[Test]
    public function expiredItemIsReportedAsMiss(): void
    {
        $item = CacheItem::create('expired')
            ->set('stale')
            ->expiresAt(new DateTimeImmutable('@1'));

        self::assertFalse($item->isHit());
        self::assertNull($item->get());
    }

    #[Test]
    public function expiresAfterAcceptsSecondsAndDateIntervals(): void
    {
        $now = microtime(true);

        $item = CacheItem::create('ttl')->expiresAfter(60);

        self::assertGreaterThanOrEqual($now + 59, $item->realExpiry);

        $now = microtime(true);
        $item->expiresAfter(new DateInterval('PT1H'));

        self::assertGreaterThanOrEqual($now + 3599, $item->realExpiry);
    }

    #[Test]
    public function negativeSecondsExpireTheItemImmediately(): void
    {
        $item = CacheItem::create('negative-ttl')->set('stale')->expiresAfter(-1);

        self::assertFalse($item->isHit());
        self::assertLessThanOrEqual(time(), $item->realExpiry);
    }

    #[Test]
    public function unrepresentableNegativeTtlIsLoggedAndPreservesTheCurrentExpiration(): void
    {
        $item = CacheItem::create('large-negative-ttl')->expiresAfter(60);
        $expiration = $item->getExpiration()->getTimestamp();
        $realExpiry = $item->realExpiry;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with(
                'CacheItem::expiresAfter() caused an error.',
                self::callback(
                    static fn(array $context): bool => $context['key'] === 'large-negative-ttl'
                        && $context['exception'] instanceof \Throwable
                )
            );
        $item->setLogger($logger);

        $item->expiresAfter(PHP_INT_MIN);

        self::assertSame($expiration, $item->getExpiration()->getTimestamp());
        self::assertSame($realExpiry, $item->realExpiry);
    }

    #[Test]
    public function expiresAtNullUsesTheDefaultExpiration(): void
    {
        $item = CacheItem::create('default-expiration')->expiresAt(null);

        self::assertGreaterThan(time(), $item->getExpiration()->getTimestamp());
        self::assertGreaterThan(microtime(true), $item->realExpiry);
    }

    #[Test]
    public function setIsHitControlsWhetherValueIsReturned(): void
    {
        $item = CacheItem::create('hit')->set('value')->setIsHit(false);

        self::assertFalse($item->isHit());
        self::assertNull($item->get());

        $item->setIsHit(true);

        self::assertSame('value', $item->get());
    }

    #[Test]
    public function tagsAreAccumulatedWithoutDuplicates(): void
    {
        $item = CacheItem::create('tagged')->tags('users', 'public')->tags('users', 'active');

        self::assertSame(['users', 'public', 'active'], array_values($item->getTags()));
    }

    #[Test]
    public function metadataValuesAreNormalized(): void
    {
        $item = CacheItem::create('metadata')->setCtime(-5)->setRealExpiry(-10);

        self::assertSame(0, $item->ctime);
        self::assertSame(0.0, $item->realExpiry);

        $item->setCtime(25)->setRealExpiry(123.5);

        self::assertSame(25, $item->ctime);
        self::assertSame(123.5, $item->realExpiry);
    }

    #[Test]
    #[DataProvider('invalidKeysProvider')]
    public function createRejectsReservedKeyCharacters(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Item key name contains reserved characters.');

        CacheItem::create($key);
    }

    /**
     * @return  array<string, array{string}>
     */
    public static function invalidKeysProvider(): array
    {
        return [
            'curly braces' => ['a{b'],
            'parentheses' => ['a(b'],
            'slash' => ['a/b'],
            'backslash' => ['a\\b'],
            'at sign' => ['a@b'],
            'colon' => ['a:b']
        ];
    }
}
