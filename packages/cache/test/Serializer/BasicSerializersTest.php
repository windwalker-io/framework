<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Serializer;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Serializer\JsonAssocSerializer;
use Windwalker\Cache\Serializer\JsonSerializer;
use Windwalker\Cache\Serializer\PhpFileSerializer;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Serializer\StringSerializer;

class BasicSerializersTest extends TestCase
{
    #[Test]
    public function jsonSerializerRoundTripsObjects(): void
    {
        $serializer = new JsonSerializer();
        $value = ['user' => ['name' => 'Ada']];

        self::assertSame('{"user":{"name":"Ada"}}', $serializer->serialize($value));
        self::assertEquals((object) ['user' => (object) ['name' => 'Ada']], $serializer->unserialize(
            $serializer->serialize($value)
        ));
    }

    #[Test]
    public function jsonAssocSerializerRoundTripsAssociativeArrays(): void
    {
        $serializer = new JsonAssocSerializer();
        $value = ['user' => ['name' => 'Ada']];

        self::assertSame($value, $serializer->unserialize($serializer->serialize($value)));
    }

    #[Test]
    public function phpSerializerRoundTripsValues(): void
    {
        $serializer = new PhpSerializer();
        $value = ['null' => null, 'number' => 42, 'nested' => ['enabled' => true]];

        self::assertSame(serialize($value), $serializer->serialize($value));
        self::assertSame($value, $serializer->unserialize($serializer->serialize($value)));
    }

    #[Test]
    public function rawSerializerPassesStringPayloadThrough(): void
    {
        $serializer = new RawSerializer();

        self::assertSame('raw payload', $serializer->serialize('raw payload'));
        self::assertSame('raw payload', $serializer->unserialize('raw payload'));
    }

    #[Test]
    public function stringSerializerAcceptsStringsAndStringableValues(): void
    {
        $serializer = new StringSerializer();
        $stringable = new class () implements \Stringable {
            public function __toString(): string
            {
                return 'stringable value';
            }
        };

        self::assertSame('plain string', $serializer->serialize('plain string'));
        self::assertSame('stringable value', $serializer->serialize($stringable));
        self::assertSame('plain string', $serializer->unserialize('plain string'));
    }

    #[Test]
    public function stringSerializerRejectsNonStringableValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('can not handle an array or non-stringable object.');

        new StringSerializer()->serialize(['not' => 'stringable']);
    }

    #[Test]
    public function phpFileSerializerCreatesReturnablePhpSource(): void
    {
        $serializer = new PhpFileSerializer();
        $payload = $serializer->serialize(['name' => 'Ada', 'enabled' => true]);

        self::assertSame("<?php \n\nreturn array (\n  'name' => 'Ada',\n  'enabled' => true,\n);", $payload);
        self::assertSame($payload, $serializer->unserialize($payload));
    }
}
