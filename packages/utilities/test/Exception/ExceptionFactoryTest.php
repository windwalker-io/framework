<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Exception\ExceptionFactory;

class ExceptionFactoryTest extends TestCase
{
    #[Test]
    public function badMethodCallUsesGivenCaller(): void
    {
        $e = ExceptionFactory::badMethodCall('foo', 'Bar');

        self::assertSame('Call to undefined method: Bar::foo()', $e->getMessage());
    }

    #[Test]
    public function badMethodCallFallsBackToCallerClassAndFunction(): void
    {
        $e = ExceptionFactory::badMethodCall('foo');

        self::assertSame(
            'Call to undefined method: ' . self::class . '::badMethodCallFallsBackToCallerClassAndFunction()::foo()',
            $e->getMessage()
        );
    }
}
