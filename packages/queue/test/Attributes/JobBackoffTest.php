<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Attributes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Attributes\JobBackoff;
use Windwalker\Queue\QueueMessage;

class JobBackoffTest extends TestCase
{
    #[Test]
    #[DataProvider('backoffProvider')]
    public function backoffCalculatesDelayByAttempt(array $args, int|false $expected): void
    {
        self::assertSame($expected, JobBackoff::backoff(...$args));
    }

    public static function backoffProvider(): array
    {
        return [
            'exponential' => [['n' => 3], 8],
            'base multiplier' => [['n' => 2, 'base' => 10], 40],
            'adaptive' => [['n' => 3, 'adaptive' => 3], 24],
            'capped by max seconds' => [['n' => 10, 'maxSeconds' => 60], 60],
            'max times reached' => [['n' => 3, 'maxTimes' => 3], false],
            'below max times' => [['n' => 2, 'maxTimes' => 3], 4],
        ];
    }

    #[Test]
    public function resolveReturnsIntOrNullBackoffAsIs(): void
    {
        self::assertSame(30, JobBackoff::resolve(2, 30));
        self::assertNull(JobBackoff::resolve(2, null));
        self::assertFalse(JobBackoff::resolve(2, false));
    }

    #[Test]
    public function resolvePicksArrayItemByAttemptNumber(): void
    {
        self::assertSame(10, JobBackoff::resolve(1, [10, 20, 30]));
        self::assertSame(30, JobBackoff::resolve(3, [10, 20, 30]));
    }

    #[Test]
    public function resolveReturnsFalseWhenAttemptsExceedArray(): void
    {
        self::assertFalse(JobBackoff::resolve(4, [10, 20, 30]));
    }

    #[Test]
    public function backoffGrowsLinearlyWhenPowIsOne(): void
    {
        self::assertSame(1, JobBackoff::backoff(0, 1, 1));
        self::assertSame(4, JobBackoff::backoff(3, 1, 1));
        self::assertSame(15, JobBackoff::backoff(2, 5, 1));
    }

    #[Test]
    public function fromControllerReadsBackoffAttributeOnJobClass(): void
    {
        $job = new #[JobBackoff([5, 10])] class () {
            public function __invoke(): void {}
        };
        $message = new QueueMessage($job)->setAttempts(2);

        self::assertSame(10, JobBackoff::fromController($message->makeJobController()));
    }
}
