<?php

declare(strict_types=1);

namespace Windwalker\Environment\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Environment\PhpHelper;

class PhpHelperTest extends TestCase
{
    #[Test]
    public function sapiHelpersMatchCurrentSapi(): void
    {
        $this->assertSame(in_array(PHP_SAPI, ['cli', 'cli-server'], true), PhpHelper::isCli());
        $this->assertSame(in_array(PHP_SAPI, ['apache', 'cgi', 'fast-cgi', 'srv'], true), PhpHelper::isWebServer());
        $this->assertSame(PHP_SAPI === 'embed', PhpHelper::isEmbed());
    }

    #[Test]
    public function runtimeIsPhpAndNotHhvm(): void
    {
        $this->assertFalse(PhpHelper::isHHVM());
        $this->assertTrue(PhpHelper::isPHP());
    }

    #[Test]
    public function getVersionReturnsPhpVersion(): void
    {
        $this->assertSame(PHP_VERSION, new PhpHelper()->getVersion());
    }

    #[Test]
    public function setStrictAndSetMutedChangeErrorReporting(): void
    {
        $original = error_reporting();

        try {
            PhpHelper::setMuted();
            $this->assertSame(0, error_reporting());

            PhpHelper::setStrict();
            $this->assertSame(-1, error_reporting());
        } finally {
            error_reporting($original);
        }
    }

    #[Test]
    public function extensionChecksMatchLoadedExtensions(): void
    {
        $this->assertSame(extension_loaded('xdebug'), new PhpHelper()->hasXdebug());
        $this->assertSame(extension_loaded('pcntl'), PhpHelper::hasPcntl());
        $this->assertSame(function_exists('curl_init'), PhpHelper::hasCurl());
        $this->assertSame(extension_loaded('mcrypt'), PhpHelper::hasMcrypt());
        $this->assertSame(extension_loaded('openssl'), PhpHelper::hasOpenssl());
    }
}
