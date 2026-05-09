<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Serializer;

use PHPUnit\Framework\TestCase;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Exception\InvalidArgumentException;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\SodiumSerializer;
use Windwalker\Cache\Storage\ArrayStorage;

class SodiumSerializerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('sodium')) {
            $this->markTestSkipped('The sodium extension is not loaded; skipping SodiumSerializer tests.');
        }
    }

    public function testSerializeAndUnserializeRoundTrip(): void
    {
        $keypair = sodium_crypto_box_keypair();
        $serializer = new SodiumSerializer($keypair, serializer: new PhpSerializer());
        $value = ['foo' => 'bar', 'nested' => ['baz' => 123]];

        $encrypted = $serializer->serialize($value);

        self::assertIsString($encrypted);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+=*$/', $encrypted);
        self::assertSame($value, $serializer->unserialize($encrypted));
    }

    public function testUnserializeSupportsRotatedOldKeys(): void
    {
        $oldKeypair = sodium_crypto_box_keypair();
        $newKeypair = sodium_crypto_box_keypair();
        $value = ['rotated' => true];

        $oldSerializer = new SodiumSerializer($oldKeypair, serializer: new PhpSerializer());
        $payload = $oldSerializer->serialize($value);

        $rotatedSerializer = new SodiumSerializer($newKeypair, [$oldKeypair], new PhpSerializer());

        self::assertIsString($payload);
        self::assertSame($value, $rotatedSerializer->unserialize($payload));
    }

    public function testUnserializeFailsWhenNoKeyMatches(): void
    {
        $keypair = sodium_crypto_box_keypair();
        $wrongKeypair = sodium_crypto_box_keypair();
        $serializer = new SodiumSerializer($keypair, serializer: new PhpSerializer());
        $payload = $serializer->serialize(['foo' => 'bar']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to decrypt sodium-encrypted cache payload');

        (new SodiumSerializer($wrongKeypair, serializer: new PhpSerializer()))->unserialize($payload);
    }

    public function testConstructorRejectsInvalidKeyLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sodium keypair must be');

        new SodiumSerializer('too-short');
    }

    public function testWorksWithCachePool(): void
    {
        $storage = new ArrayStorage();
        $keypair = sodium_crypto_box_keypair();
        $pool = new CachePool($storage, new SodiumSerializer($keypair, serializer: new JsonAssocSerializer()));

        self::assertTrue($pool->set('encrypted_item', ['foo' => 'bar'], 3600));

        $rawStored = $storage->get('encrypted_item');
        self::assertIsString($rawStored);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+=*$/', $rawStored);
        self::assertSame(['foo' => 'bar'], $pool->get('encrypted_item'));
    }
}

