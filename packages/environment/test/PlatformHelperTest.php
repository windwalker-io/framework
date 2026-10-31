<?php

declare(strict_types=1);

namespace Windwalker\Environment\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Environment\Environment;
use Windwalker\Environment\PlatformHelper;

/**
 * @deprecated Covers the deprecated PlatformHelper; remove together with it.
 */
class PlatformHelperTest extends TestCase
{
    #[After]
    protected function resetStaticPlatformState(): void
    {
        Environment::setOS('');
        Environment::setUname(PHP_OS);
    }

    #[Test]
    public function getEnvironmentReturnsSharedInstance(): void
    {
        $this->assertSame(PlatformHelper::getEnvironment(), PlatformHelper::getEnvironment());
    }

    #[Test]
    public function platformChecksDelegateToEnvironment(): void
    {
        Environment::setOS('LIN');

        $this->assertTrue(PlatformHelper::isLinux());
        $this->assertTrue(PlatformHelper::isUnix());
        $this->assertFalse(PlatformHelper::isWindows());

        Environment::setOS('WIN');

        $this->assertTrue(PlatformHelper::isWindows());
        $this->assertFalse(PlatformHelper::isUnix());
    }
}
