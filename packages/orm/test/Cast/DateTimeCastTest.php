<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Cast;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\ORM\Cast\DateTimeCast;

/**
 * The DateTimeCastTest class.
 */
class DateTimeCastTest extends TestCase
{
    private DateTimeCast $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = new DateTimeCast();
    }

    #[Test]
    public function hydrateReturnsNullForNull(): void
    {
        self::assertNull($this->instance->hydrate(null));
    }

    #[Test]
    public function hydrateKeepsExistingDateTimeInstance(): void
    {
        $date = new DateTimeImmutable('2024-01-02 03:04:05');

        self::assertSame($date, $this->instance->hydrate($date));
    }

    #[Test]
    public function hydrateParsesDateOnlyString(): void
    {
        $date = $this->instance->hydrate('2024-1-2');

        self::assertSame('2024-01-02', $date->format('Y-m-d'));
    }

    #[Test]
    public function hydrateParsesFullDateTimeString(): void
    {
        $date = $this->instance->hydrate('2024-01-02 03:04:05');

        self::assertSame('2024-01-02 03:04:05', $date->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function extractReturnsNullForNull(): void
    {
        self::assertNull($this->instance->extract(null));
    }

    #[Test]
    public function extractFormatsDateTimeAndStringifiesOthers(): void
    {
        self::assertSame(
            '2024-01-02 03:04:05',
            $this->instance->extract(new DateTimeImmutable('2024-01-02 03:04:05'))
        );
        self::assertSame('2024-01-02', $this->instance->extract('2024-01-02'));
    }
}
