<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\SettledResult;

class SettledResultTest extends TestCase
{
    #[Test]
    public function fulfilledCreatesFulfilledResultWithValue(): void
    {
        $result = SettledResult::fulfilled('A');

        self::assertSame(PromiseState::FULFILLED, $result->status);
        self::assertSame('A', $result->value);
    }

    #[Test]
    public function rejectedCreatesRejectedResultWithReason(): void
    {
        $result = SettledResult::rejected('B');

        self::assertSame(PromiseState::REJECTED, $result->status);
        self::assertSame('B', $result->value);
    }
}
