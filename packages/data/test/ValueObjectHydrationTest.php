<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\ValueObject;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;

class ValueObjectHydrationTest extends TestCase
{
    #[Test]
    public function itWrapsExistingInstancesAndRawData(): void
    {
        $vo = new HydrationVO(['title' => 'a']);

        self::assertSame($vo, HydrationVO::wrap($vo));
        self::assertSame('A', HydrationVO::wrap(['title' => 'a'])->title);
    }

    #[Test]
    public function itUsesSettersAndCastAttributes(): void
    {
        $vo = new HydrationVO([
            'title' => 'abc',
            'count' => '5',
            'date' => '2020-01-01',
            'created_at' => '2021-01-01',
            'nullable' => null,
            'unknown_prop' => 1,
        ]);

        self::assertSame('ABC', $vo->title);
        self::assertSame(5, $vo->count);
        self::assertInstanceOf(\DateTimeImmutable::class, $vo->date);
        self::assertSame('2021-01-01', $vo->createdAt);
        self::assertNull($vo->nullable);
        self::assertSame(1, $vo->unknownProp);
    }
}

#[\AllowDynamicProperties]
class HydrationVO extends ValueObject
{
    public string $title = '';

    #[Cast('intval')]
    public int $count = 0;

    #[Cast(\DateTimeImmutable::class)]
    public ?\DateTimeImmutable $date = null;

    public string $createdAt = '';

    #[CastNullable('intval')]
    public ?int $nullable = 1;

    public function setTitle(string $title): void
    {
        $this->title = strtoupper($title);
    }
}
