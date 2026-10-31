<?php

declare(strict_types=1);

namespace Windwalker\Environment\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Environment\Environment;
use Windwalker\Environment\PhpHelper;

class EnvironmentTest extends TestCase
{
    protected Environment $instance;

    #[Before]
    protected function createEnvironment(): void
    {
        $this->instance = new Environment();
    }

    #[After]
    protected function resetStaticPlatformState(): void
    {
        Environment::setOS('');
        Environment::setUname(PHP_OS);
    }

    public static function windowsDetectionProvider(): array
    {
        return [
            ['CYGWIN_NT-5.1', false],
            ['Darwin', false],
            ['FreeBSD', false],
            ['HP-UX', false],
            ['IRIX64', false],
            ['Linux', false],
            ['NetBSD', false],
            ['OpenBSD', false],
            ['SunOS', false],
            ['Unix', false],
            ['WIN32', true],
            ['WINNT', true],
            ['Windows', true],
        ];
    }

    public static function unixDetectionProvider(): array
    {
        return [
            ['CYGWIN_NT-5.1', true],
            ['Darwin', true],
            ['FreeBSD', true],
            ['HP-UX', true],
            ['IRIX64', true],
            ['Linux', true],
            ['NetBSD', true],
            ['OpenBSD', true],
            ['SunOS', true],
            ['Unix', true],
            ['WIN32', false],
            ['WINNT', false],
            ['Windows', false],
        ];
    }

    public static function linuxDetectionProvider(): array
    {
        return [
            ['CYGWIN_NT-5.1', false],
            ['Darwin', false],
            ['FreeBSD', false],
            ['HP-UX', false],
            ['IRIX64', false],
            ['Linux', true],
            ['NetBSD', false],
            ['OpenBSD', false],
            ['SunOS', false],
            ['Unix', false],
            ['WIN32', false],
            ['WINNT', false],
            ['Windows', false],
        ];
    }

    #[Test]
    public function getOsReturnsShortCodeFromUname(): void
    {
        $this->instance->setUname('Darwin');

        $this->assertEquals('DAR', $this->instance->getOS());
    }

    #[Test]
    #[DataProvider('windowsDetectionProvider')]
    public function isWindowsDetectsPlatformFromUname(string $os, bool $value): void
    {
        $this->instance->setOS('');
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isWindows());
    }

    #[Test]
    #[DataProvider('unixDetectionProvider')]
    public function isUnixDetectsPlatformFromUname(string $os, bool $value): void
    {
        $this->instance->setOS('');
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isUnix());
    }

    #[Test]
    #[DataProvider('linuxDetectionProvider')]
    public function isLinuxDetectsPlatformFromUname(string $os, bool $value): void
    {
        $this->instance->setOS('');
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isLinux());
    }

    #[Test]
    public function setOsUppercasesAndOverridesDetection(): void
    {
        Environment::setOS('win');

        $this->assertSame('WIN', Environment::getOS());
        $this->assertTrue(Environment::isWindows());
    }

    #[Test]
    public function getUnameReturnsConfiguredValue(): void
    {
        Environment::setUname('Linux');

        $this->assertSame('Linux', Environment::getUname());
    }

    #[Test]
    public function cliHelpersReflectCurrentSapi(): void
    {
        $this->assertSame(in_array(PHP_SAPI, ['cli', 'cli-server'], true), Environment::isCli());
        $this->assertSame(PhpHelper::isWebServer(), Environment::isWebServer());
    }

    #[Test]
    public function serverParamsCanBeReadAndReplaced(): void
    {
        $env = new Environment(['A' => '1']);

        $this->assertSame(['A' => '1'], $env->getServerParams());
        $this->assertSame($env, $env->setServerParams(['B' => '2']));
        $this->assertSame(['B' => '2'], $env->getServerParams());
    }

    #[Test]
    public function constructorFallsBackToGlobalServer(): void
    {
        $this->assertSame($_SERVER, (new Environment())->getServerParams());
    }

    #[Test]
    public function getUserHomeDirPrefersHomeThenDriveAndPath(): void
    {
        $this->assertSame('/home/me', (new Environment(['HOME' => '/home/me']))->getUserHomeDir());
        $this->assertSame(
            'C:\\Users\\me',
            (new Environment(['HOMEDRIVE' => 'C:', 'HOMEPATH' => '\\Users\\me']))->getUserHomeDir()
        );
    }

    #[Test]
    public function getUserNameFallsBackToUsername(): void
    {
        $this->assertSame('simon', (new Environment(['USER' => 'simon', 'USERNAME' => 'other']))->getUserName());
        $this->assertSame('other', (new Environment(['USERNAME' => 'other']))->getUserName());
        $this->assertNull((new Environment([]))->getUserName());
    }

    #[Test]
    public function getDeviceNameReadsUserDomain(): void
    {
        $this->assertSame('my-pc', (new Environment(['USERDOMAIN' => 'my-pc']))->getDeviceName());
        $this->assertNull((new Environment([]))->getDeviceName());
    }

    #[Test]
    public function directoryHelpersReturnSystemValues(): void
    {
        $this->assertSame(getcwd(), $this->instance->getWorkingDirectory());
        $this->assertSame(sys_get_temp_dir(), $this->instance->getTempDir());
    }

    #[Test]
    public function getEntryStripsWorkingDirectoryAndRebuildsFullPathInCli(): void
    {
        $cwd = getcwd();
        $env = new Environment(['SCRIPT_FILENAME' => $cwd . '/bin/app.php', 'SCRIPT_NAME' => './app.php']);

        $this->assertSame($cwd . DIRECTORY_SEPARATOR . 'bin/app.php', $env->getEntry());
        $this->assertSame('app.php', $env->getEntry(false));
    }

    #[Test]
    public function getRootReturnsEntryDirectory(): void
    {
        $cwd = getcwd();
        $env = new Environment(['SCRIPT_FILENAME' => $cwd . '/bin/app.php']);

        $this->assertSame($cwd . DIRECTORY_SEPARATOR . 'bin', $env->getRoot());
    }

    #[Test]
    #[DataProvider('entryNameProvider')]
    public function getEntryTrimsBothLeadingSeparators(string $entry): void
    {
        $env = new Environment(['SCRIPT_NAME' => $entry]);

        self::assertSame('app.php', $env->getEntry(false));
    }

    public static function entryNameProvider(): array
    {
        return [
            'forward slash' => ['./app.php'],
            'backslash' => ['.\\app.php']
        ];
    }

    #[Test]
    public function requestHelpersReadServerParams(): void
    {
        $env = new Environment([
            'DOCUMENT_ROOT' => '/var/www',
            'REQUEST_URI' => '/foo?a=1',
            'PHP_SELF' => '/foo',
            'HTTP_HOST' => 'example.com',
            'SERVER_PORT' => '443',
            'REQUEST_SCHEME' => 'https',
        ]);

        $this->assertSame('/var/www', $env->getServerPublicRoot());
        $this->assertSame('/foo?a=1', $env->getRequestUri());
        $this->assertSame('/foo', $env->getRequestUri(false));
        $this->assertSame('example.com', $env->getHost());
        $this->assertSame('443', $env->getPort());
        $this->assertSame('https', $env->getScheme());
        $this->assertTrue($env->isSSL());
        $this->assertFalse((new Environment(['REQUEST_SCHEME' => 'http']))->isSSL());
    }
}
