<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;

class MapProxyTest extends TestCase
{
    #[Test]
    public function itMapsAColumnWithoutChangingTheOriginalCollection(): void
    {
        $collection = new Collection([
            ['score' => 2],
            ['score' => 5]
        ]);

        $updated = $collection->mapProxy('score')->map(
            static fn(int $score): int => $score * 2
        );

        self::assertSame([['score' => 2], ['score' => 5]], $collection->dump());
        self::assertSame([['score' => 4], ['score' => 10]], $updated->dump());
    }

    #[Test]
    public function itMapsAMethodAcrossItems(): void
    {
        $collection = new Collection([new MapProxyLabel('one'), new MapProxyLabel('two')]);

        $labels = $collection->mapProxy()->label();

        self::assertSame(['one!', 'two!'], $labels->dump());
    }
}

class MapProxyLabel
{
    public function __construct(private readonly string $label) {}

    public function label(): string
    {
        return $this->label . '!';
    }
}
