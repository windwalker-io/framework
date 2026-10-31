<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use DateTimeImmutable;
use JsonSerializable;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\ORM\BaseCaster;

/**
 * The BaseCasterTest class.
 */
class BaseCasterTest extends TestCase
{
    private BaseCaster $instance;

    #[Before]
    protected function createInstance(): void
    {
        $db = $this->createStub(DatabaseAdapter::class);
        $db->method('getDateFormat')->willReturn('Y-m-d H:i:s');

        $this->instance = new BaseCaster($db);
    }

    #[Test]
    public function castDateTimeFormatsWithDatabaseFormat(): void
    {
        self::assertSame(
            '2024-01-02 03:04:05',
            $this->instance->castDateTime(new DateTimeImmutable('2024-01-02 03:04:05'))
        );
    }

    #[Test]
    public function castJsonSerializableEncodesToJson(): void
    {
        $value = new class () implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['a' => 1];
            }
        };

        self::assertSame('{"a":1}', $this->instance->castJsonSerializable($value));
    }

    #[Test]
    public function castValueStringifiesStringableObjects(): void
    {
        $value = new class () {
            public function __toString(): string
            {
                return 'foo';
            }
        };

        self::assertSame('foo', $this->instance->castValue($value));
    }

    #[Test]
    public function castValueResetsArraysAndPlainObjectsToNull(): void
    {
        self::assertNull($this->instance->castValue([1]));
        self::assertNull($this->instance->castValue(new \stdClass()));
        self::assertSame(5, $this->instance->castValue(5));
    }

    #[Test]
    public function castDateTimeConvertsToDatabaseTimezone(): void
    {
        $this->instance->setDbTimezone('Asia/Taipei');

        self::assertSame(
            '2024-01-02 11:00:00',
            $this->instance->castDateTime(new DateTimeImmutable('2024-01-02 03:00:00', new \DateTimeZone('UTC')))
        );
        self::assertSame('Asia/Taipei', $this->instance->getDbTimezone()->getName());

        $this->instance->setDbTimezone(null);

        self::assertSame(
            '2024-01-02 03:00:00',
            $this->instance->castDateTime(new DateTimeImmutable('2024-01-02 03:00:00', new \DateTimeZone('UTC')))
        );
    }

    #[Test]
    public function castEnumUnwrapsBackedValues(): void
    {
        self::assertSame('a', $this->instance->castEnum(BaseCasterStubEnum::A));
    }
}

enum BaseCasterStubEnum: string
{
    case A = 'a';
}
