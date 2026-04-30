<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Serializer;

use PHPUnit\Framework\TestCase;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Serializer\DeflateSerializer;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Storage\ArrayStorage;

class DeflateSerializerTest extends TestCase
{
    public function testSerializeAndUnserializeRoundTrip(): void
    {
        $serializer = new DeflateSerializer(new PhpSerializer());
        $value = ['foo' => 'bar', 'nested' => ['baz' => 123]];

        $compressed = $serializer->serialize($value);

        self::assertIsString($compressed);
        self::assertNotSame(serialize($value), $compressed);
        self::assertSame($value, $serializer->unserialize($compressed));
    }

    public function testUnserializeFallsBackToOriginalPayloadWhenValueIsNotCompressed(): void
    {
        $inner = new PhpSerializer();
        $serializer = new DeflateSerializer($inner);
        $value = ['legacy' => true];

        $raw = $inner->serialize($value);

        self::assertIsString($raw);
        self::assertSame($value, $serializer->unserialize($raw));
    }

    public function testWorksWithCachePool(): void
    {
        $storage = new ArrayStorage();
        $pool = new CachePool($storage, new DeflateSerializer(new JsonAssocSerializer()));

        self::assertTrue($pool->set('compressed_item', ['foo' => 'bar'], 3600));

        $rawStored = $storage->get('compressed_item');
        self::assertIsString($rawStored);
        self::assertNotSame('{"foo":"bar"}', $rawStored);
        self::assertSame(['foo' => 'bar'], $pool->get('compressed_item'));
    }
}

