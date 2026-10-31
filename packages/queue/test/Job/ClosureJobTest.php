<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Job;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Attributes\JobBackoff;
use Windwalker\Queue\Job\ClosureJob;
use Windwalker\Queue\Job\JobController;

class ClosureJobTest extends TestCase
{
    #[Test]
    public function invokePassesControllerToWrappedClosure(): void
    {
        $received = null;
        $job = new ClosureJob(function (JobController $controller) use (&$received) {
            $received = $controller;
        });
        $controller = $this->createStub(JobController::class);

        $job($controller);

        self::assertSame($controller, $received);
    }

    #[Test]
    public function backoffReturnsAttributeValueFromClosure(): void
    {
        $job = new ClosureJob(#[JobBackoff([5, 10])] static function () {});

        self::assertSame([5, 10], $job->backoff());
    }

    #[Test]
    public function backoffReturnsNullWithoutAttribute(): void
    {
        $job = new ClosureJob(static function () {});

        self::assertNull($job->backoff());
    }

    #[Test]
    public function jobClosureIsRecoverableFromSerializableWrapper(): void
    {
        $job = new ClosureJob(static fn() => 'x');

        self::assertSame('x', ($job->getJobClosure())());
        self::assertSame('x', ($job->getJob()->getClosure())());
    }
}
