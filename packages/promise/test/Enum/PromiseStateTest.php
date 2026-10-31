<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Promise\Enum\PromiseState;

class PromiseStateTest extends TestCase
{
    public static function stateFlagsProvider(): array
    {
        return [
            'pending' => [PromiseState::PENDING, false, false, false, true],
            'fulfilled' => [PromiseState::FULFILLED, true, true, false, false],
            'rejected' => [PromiseState::REJECTED, true, false, true, false],
        ];
    }

    #[Test]
    #[DataProvider('stateFlagsProvider')]
    public function stateFlagsReflectOnlyTheirOwnState(
        PromiseState $state,
        bool $settled,
        bool $fulfilled,
        bool $rejected,
        bool $pending
    ): void {
        self::assertSame($settled, $state->isSettled());
        self::assertSame($fulfilled, $state->isFulfilled());
        self::assertSame($rejected, $state->isRejected());
        self::assertSame($pending, $state->isPending());
    }
}
