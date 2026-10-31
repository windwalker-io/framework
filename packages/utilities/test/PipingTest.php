<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Piping;

use function Windwalker\piping;

class PipingTest extends TestCase
{
    #[Test]
    public function pipeReplacesValueWithCallbackResultAndPassesExtraArgs(): void
    {
        $piping = piping(2)
            ->pipe(fn(int $v, int $add) => $v + $add, 3)
            ->pipe(fn(int $v) => $v * 2);

        self::assertInstanceOf(Piping::class, $piping);
        self::assertSame(10, $piping->value);
    }

    #[Test]
    public function tapDoesNotChangeValue(): void
    {
        $seen = [];
        $piping = piping('abc')->tap(function (string $v, string $extra) use (&$seen) {
            $seen = [$v, $extra];

            return 'ignored';
        }, 'x');

        self::assertSame('abc', $piping->value);
        self::assertSame(['abc', 'x'], $seen);
    }

    #[Test]
    public function countReturnsZeroForNonCountableValues(): void
    {
        self::assertCount(3, piping([1, 2, 3]));
        self::assertCount(0, piping('string'));
    }

    #[Test]
    public function toStringCastsValue(): void
    {
        self::assertSame('123', (string) piping(123));
    }
}
