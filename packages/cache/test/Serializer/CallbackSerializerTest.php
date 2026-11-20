<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Serializer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Serializer\CallbackSerializer;

class CallbackSerializerTest extends TestCase
{
    #[Test]
    public function serializeAndUnserializeUseProvidedCallbacks(): void
    {
        $serializer = new CallbackSerializer(
            serializer: static fn(array $data): string => strtoupper(implode(',', $data)),
            unserializer: static fn(string $data): array => explode(',', strtolower($data)),
        );

        $value = ['foo', 'bar'];

        self::assertSame('FOO,BAR', $serializer->serialize($value));
        self::assertSame($value, $serializer->unserialize($serializer->serialize($value)));
    }

    #[Test]
    public function callbacksReceiveDataUnchanged(): void
    {
        $received = [];

        $serializer = new CallbackSerializer(
            serializer: function (mixed $data) use (&$received): mixed {
                $received[] = $data;

                return $data;
            },
            unserializer: function (mixed $data) use (&$received): mixed {
                $received[] = $data;

                return $data;
            },
        );

        self::assertNull($serializer->serialize(null));
        self::assertSame(['payload'], [$serializer->unserialize('payload')]);
        self::assertSame([null, 'payload'], $received);
    }

    #[Test]
    public function acceptsFirstClassCallables(): void
    {
        $serializer = new CallbackSerializer(strrev(...), strrev(...));

        self::assertSame('olleh', $serializer->serialize('hello'));
        self::assertSame('hello', $serializer->unserialize('olleh'));
    }
}
