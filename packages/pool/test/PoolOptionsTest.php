<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\PoolOptions;

class PoolOptionsTest extends TestCase
{
    #[Test]
    #[DataProvider('sizeProvider')]
    public function constructorNormalizesSizes(
        int $maxSize,
        int $minSize,
        int $expectedMax,
        int $expectedMin
    ): void {
        $options = new PoolOptions(maxSize: $maxSize, minSize: $minSize);

        self::assertSame($expectedMax, $options->maxSize);
        self::assertSame($expectedMin, $options->minSize);
    }

    public static function sizeProvider(): array
    {
        return [
            'max below one is raised to one' => [0, 1, 1, 1],
            'min above max is capped to max' => [3, 10, 3, 3],
            'negative min is raised to zero' => [3, -5, 3, 0],
            'valid values are kept' => [5, 2, 5, 2],
        ];
    }
}
