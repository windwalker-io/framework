<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Windwalker\go;
use function Windwalker\run;
use function Windwalker\swoole_in_coroutine;
use function Windwalker\swoole_installed;

class FunctionsTest extends TestCase
{
    #[Test]
    public function swooleInstalledMatchesExtensionState(): void
    {
        self::assertSame(extension_loaded('swoole'), swoole_installed());
    }

    #[Test]
    public function goRunsHandlerSynchronouslyWithoutSwoole(): void
    {
        if (swoole_installed()) {
            $this->markTestSkipped('Fallback only applies when Swoole is not installed.');
        }

        self::assertSame('done', go(static fn() => 'done'));
    }

    #[Test]
    public function runRunsHandlerSynchronouslyWithoutSwoole(): void
    {
        if (swoole_installed()) {
            $this->markTestSkipped('Fallback only applies when Swoole is not installed.');
        }

        self::assertSame('done', run(static fn() => 'done'));
    }

    #[Test]
    public function swooleInCoroutineIsFalseOutsideCoroutine(): void
    {
        self::assertFalse(swoole_in_coroutine());
    }
}
