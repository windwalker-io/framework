<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Promise\Exception\UncaughtException;

class UncaughtExceptionTest extends TestCase
{
    #[Test]
    public function throwableReasonProvidesMessageAndPrevious(): void
    {
        $reason = new RuntimeException('Boom', 42);

        $e = new UncaughtException($reason);

        self::assertSame('Boom', $e->getMessage());
        self::assertSame($reason, $e->getPrevious());
    }

    #[Test]
    public function scalarReasonIsCastToMessage(): void
    {
        $e = new UncaughtException(123);

        self::assertSame('123', $e->getMessage());
    }

    #[Test]
    public function nonScalarReasonHasEmptyMessage(): void
    {
        $e = new UncaughtException(['a']);

        self::assertSame('', $e->getMessage());
    }
}
