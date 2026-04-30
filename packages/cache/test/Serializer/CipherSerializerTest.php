<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Serializer;

use PHPUnit\Framework\TestCase;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Serializer\CipherSerializer;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\Symmetric\CipherInterface;

class CipherSerializerTest extends TestCase
{
    public function testSerializeAndUnserializeRoundTripUsingCipherInterface(): void
    {
        $key = 'secret-key';
        $encoder = 'base64url';

        $cipher = $this->createMock(CipherInterface::class);
        $cipher->expects(self::once())
            ->method('encrypt')
            ->with(serialize(['foo' => 'bar']), $key, $encoder)
            ->willReturn('ENC_PAYLOAD');
        $cipher->expects(self::once())
            ->method('decrypt')
            ->with('ENC_PAYLOAD', $key, $encoder)
            ->willReturn(new HiddenString(serialize(['foo' => 'bar'])));

        $serializer = new CipherSerializer($cipher, $key, new PhpSerializer(), $encoder);

        $encrypted = $serializer->serialize(['foo' => 'bar']);

        self::assertSame('ENC_PAYLOAD', $encrypted);
        self::assertSame(['foo' => 'bar'], $serializer->unserialize($encrypted));
    }

    public function testAcceptsKeyObject(): void
    {
        $key = Key::wrap('object-key');

        $cipher = $this->createMock(CipherInterface::class);
        $cipher->expects(self::once())
            ->method('encrypt')
            ->with('abc', $key, 'base64url')
            ->willReturn('enc');
        $cipher->expects(self::once())
            ->method('decrypt')
            ->with('enc', $key, 'base64url')
            ->willReturn(new HiddenString('abc'));

        $serializer = new CipherSerializer($cipher, $key, new RawSerializer());

        self::assertSame('enc', $serializer->serialize('abc'));
        self::assertSame('abc', $serializer->unserialize('enc'));
    }

    public function testWorksWithCachePool(): void
    {
        $cipher = new class implements CipherInterface {
            public function decrypt(
                string $str,
                Key|string $key,
                string|callable $encoder = 'base64url'
            ): HiddenString {
                $raw = (string) base64_decode($str, true);
                $prefix = Key::strip($key) . '|';

                return new HiddenString(str_starts_with($raw, $prefix) ? substr($raw, strlen($prefix)) : '');
            }

            public function encrypt(
                HiddenString|string $str,
                Key|string $key,
                string|callable $encoder = 'base64url'
            ): string {
                return base64_encode(Key::strip($key) . '|' . HiddenString::strip($str));
            }

            public static function generateKey(?int $length = null): Key
            {
                return Key::wrap('generated-key');
            }
        };

        $storage = new ArrayStorage();
        $pool = new CachePool($storage, new CipherSerializer($cipher, 'pool-key', new JsonAssocSerializer()));

        self::assertTrue($pool->set('cipher_item', ['foo' => 'bar'], 3600));

        $rawStored = $storage->get('cipher_item');
        self::assertIsString($rawStored);
        self::assertNotSame('{"foo":"bar"}', $rawStored);
        self::assertSame(['foo' => 'bar'], $pool->get('cipher_item'));
    }
}


