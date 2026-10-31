<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Cast;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\ORM\Cast\TimestampCast;

/**
 * The TimestampCastTest class.
 */
class TimestampCastTest extends TestCase
{
    protected TimestampCast $instance;

    #[Before]
    protected function createCast(): void
    {
        $this->instance = new TimestampCast();
    }

    #[Test]
    public function hydrateKeepsNull(): void
    {
        self::assertNull($this->instance->hydrate(null));
    }

    #[Test]
    #[DataProvider('numericTimestampProvider')]
    public function hydrateConvertsNumericValuesToNumbers(mixed $value, int|float $expected): void
    {
        self::assertSame($expected, $this->instance->hydrate($value));
    }

    public static function numericTimestampProvider(): array
    {
        return [
            'numeric string' => ['1600000000', 1600000000],
            'integer' => [1600000000, 1600000000],
        ];
    }

    #[Test]
    public function hydrateConvertsDateStringToTimestamp(): void
    {
        self::assertSame(
            1600000000,
            $this->instance->hydrate('2020-09-13 12:26:40 UTC')
        );
    }

    #[Test]
    public function extractKeepsNull(): void
    {
        self::assertNull($this->instance->extract(null));
    }

    #[Test]
    public function extractFormatsTimestampAsDateTimeString(): void
    {
        self::assertSame('2020-09-13 12:26:40', $this->instance->extract('1600000000'));
    }
}
