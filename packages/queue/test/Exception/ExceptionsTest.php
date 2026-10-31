<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Exception\AbandonedException;
use Windwalker\Queue\Exception\DeferredException;
use Windwalker\Queue\Exception\UnrecoverableException;

class ExceptionsTest extends TestCase
{
    #[Test]
    public function abandonedFromWrapsPreviousException(): void
    {
        $previous = new \LogicException('oops', 5);

        $e = AbandonedException::from($previous);

        self::assertSame('oops', $e->getMessage());
        self::assertSame(5, $e->getCode());
        self::assertSame($previous, $e->getPrevious());
    }

    #[Test]
    public function abandonedThrowFromThrowsWrappedException(): void
    {
        $this->expectException(AbandonedException::class);
        $this->expectExceptionMessageIs('oops');

        AbandonedException::throwFrom(new \LogicException('oops'));
    }

    #[Test]
    public function abandonedReasonTextIncludesMessageWhenPresent(): void
    {
        self::assertSame('Job abandoned, reason: bad', new AbandonedException('bad')->toReasonText());
        self::assertSame('Job abandoned.', new AbandonedException()->toReasonText());
    }

    #[Test]
    public function deferredFromKeepsDelayAndPreviousException(): void
    {
        $previous = new \LogicException('later', 3);

        $e = DeferredException::from(30, $previous);

        self::assertSame(30, $e->delay);
        self::assertSame('later', $e->getMessage());
        self::assertSame(3, $e->getCode());
        self::assertSame($previous, $e->getPrevious());
    }

    #[Test]
    public function deferredThrowFromThrowsWithDelay(): void
    {
        try {
            DeferredException::throwFrom(15, new \LogicException('x'));
        } catch (DeferredException $e) {
            self::assertSame(15, $e->delay);

            return;
        }

        self::fail('DeferredException was not thrown.');
    }

    #[Test]
    public function deferredReasonTextDescribesDelay(): void
    {
        self::assertSame(
            'released after 10 seconds, reason: busy',
            new DeferredException(10, 'busy')->getReasonText()
        );
        self::assertSame('released after 10 seconds.', new DeferredException(10)->getReasonText());
    }

    #[Test]
    public function unrecoverableFromWrapsPreviousException(): void
    {
        $previous = new \LogicException('fatal', 7);

        $e = UnrecoverableException::from($previous);

        self::assertSame('fatal', $e->getMessage());
        self::assertSame(7, $e->getCode());
        self::assertSame($previous, $e->getPrevious());
    }

    #[Test]
    public function unrecoverableThrowFromThrowsWrappedException(): void
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessageIs('fatal');

        UnrecoverableException::throwFrom(new \LogicException('fatal'));
    }
}
