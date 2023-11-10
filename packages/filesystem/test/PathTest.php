<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\File;
use Windwalker\Filesystem\Path;

/**
 * Test class of Path
 *
 * @since 2.0
 */
class PathTest extends AbstractVfsTestCase
{
    /**
     * Data provider for testClean() method.
     *
     * @return  array
     *
     * @since   2.0
     */
    public static function cleanProvider(): array
    {
        return [
            // Input Path, Directory Separator, Expected Output
            'Nothing to do.' => ['/var/www/foo/bar/baz', '/', '/var/www/foo/bar/baz'],
            'One backslash.' => ['/var/www/foo\\bar/baz', '/', '/var/www/foo/bar/baz'],
            'Two and one backslashes.' => ['/var/www\\\\foo\\bar/baz', '/', '/var/www/foo/bar/baz'],
            'Mixed backslashes and double forward slashes.' => [
                '/var\\/www//foo\\bar/baz',
                '/',
                '/var/www/foo/bar/baz',
            ],
            'UNC path.' => ['\\\\www\\docroot', '\\', '\\\\www\\docroot'],
            'UNC path with forward slash.' => ['\\\\www/docroot', '\\', '\\\\www\\docroot'],
            'UNC path with UNIX directory separator.' => ['\\\\www/docroot', '/', '/www/docroot'],
            'Stream URL.' => ['vfs://files//foo\\bar', '/', 'vfs://files/foo/bar'],
            'Stream URL empty.' => ['vfs://', '/', 'vfs://'],
            'Windows path.' => ['C:\\files\\\\foo//bar', '\\', 'C:\\files\\foo\\bar'],
            'Windows path empty.' => ['C:\\', '\\', 'C:\\'],
            'Windows path empty 2.' => ['C:/', '\\', 'C:\\'],
        ];
    }

    /**
     * Method to test setPermissions().
     *
     * @return void
     *
     * @covers \Windwalker\Filesystem\Path::setPermissions
     * @TODO   Implement testSetPermissions().
     */
    public function testSetPermissions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getPermissions().
     *
     * @return void
     *
     * @covers \Windwalker\Filesystem\Path::getPermissions
     * @TODO   Implement testGetPermissions().
     */
    public function testGetPermissions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test clean().
     *
     * @param  string  $input
     * @param  string  $ds
     * @param  string  $expected
     *
     * @return void
     *
     * @covers        \Windwalker\Filesystem\Path::clean
     */
    #[DataProvider('cleanProvider')]
    public function testClean(string $input, string $ds, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Path::clean($input, $ds)
        );
    }

    /**
     * testExistsInsensitive
     *
     * @param  string  $path
     * @param  bool    $sExists
     * @param  bool    $iExists
     *
     * @return void
     */
    #[DataProvider('existsProvider')]
    public function testExists(string $path, bool $sExists, bool $iExists): void
    {
        self::assertSame($sExists, Path::exists($path, Path::CASE_SENSITIVE));
        self::assertSame($iExists, Path::exists($path, Path::CASE_INSENSITIVE));
    }

    /**
     * existsProvider
     *
     * @return  array
     */
    public static function existsProvider(): array
    {
        return [
            [
                __DIR__ . '/case/Flower/saKura/test.txt',
                false,
                true,
            ],
            [
                __DIR__ . '/case/Flower/saKura/TEST.txt',
                true,
                true,
            ],
            [
                __DIR__ . '/case/Flower/sakura',
                false,
                true,
            ],
            [
                __DIR__ . '/case/Flower/Olive',
                false,
                false,
            ],
            [
                'vfs://root/files',
                true,
                true,
            ],
        ];
    }

    /**
     * testFixCase
     *
     * @return  void
     */
    public function testFixCase()
    {
        $path = __DIR__ . '/case/Flower/saKura/test.txt';

        self::assertEquals(Path::clean(__DIR__ . '/case/Flower/saKura/TEST.txt'), Path::fixCase($path));
    }

    /**
     * Method to test stripExtension().
     *
     * @return void
     */
    public function testStripExtension()
    {
        $name = Path::stripExtension('Wu-la.la');

        $this->assertEquals('Wu-la', $name);

        $name = Path::stripExtension(__DIR__ . '/Wu-la.la');

        $this->assertEquals(__DIR__ . '/Wu-la', $name);
    }

    /**
     * Method to test getExtension().
     *
     * @return void
     */
    public function testGetExtension()
    {
        $ext = Path::getExtension('Wu-la.la');

        $this->assertEquals('la', $ext);
    }

    /**
     * Method to test getFilename().
     *
     * @return void
     */
    public function testGetFilename()
    {
        $name = Path::getFilename(__DIR__ . '/Wu-la.la');

        $this->assertEquals('Wu-la.la', $name);
    }

    /**
     * Provides the data to test the makeSafe method.
     *
     * @return  array
     *
     * @since   2.0
     */
    public static function makeSafeProvider(): array
    {
        return [
            [
                'windwalker.',
                ['#^\.#'],
                'windwalker',
                'There should be no fullstop on the end of a filename',
            ],
            [
                'Test w1ndwa1ker_5-1.html',
                ['#^\.#'],
                'Test w1ndwa1ker_5-1.html',
                'Alphanumeric symbols, dots, dashes, spaces and underscores should not be filtered',
            ],
            [
                'Test w1ndwa1ker_5-1.html',
                ['#^\.#', '/\s+/'],
                'Testw1ndwa1ker_5-1.html',
                'Using strip chars parameter here to strip all spaces',
            ],
            [
                'windwalker.php!.',
                ['#^\.#'],
                'windwalker.php',
                'Non-alphanumeric symbols should be filtered to avoid disguising file extensions',
            ],
            [
                'windwalker.php.!',
                ['#^\.#'],
                'windwalker.php',
                'Non-alphanumeric symbols should be filtered to avoid disguising file extensions',
            ],
            [
                '.gitignore',
                [],
                '.gitignore',
                'Files starting with a fullstop should be allowed when strip chars parameter is empty',
            ],
        ];
    }

    /**
     * Method to test makeSafe().
     *
     * @param  string  $name        The name of the file to test filtering of
     * @param  array   $stripChars  Whether to filter spaces out the name or not
     * @param  string  $expected    The expected safe file name
     * @param  string  $message     The message to show on failure of test
     *
     * @return void
     */
    #[DataProvider('makeSafeProvider')]
    public function testMakeSafe($name, $stripChars, $expected, $message)
    {
        $this->assertEquals(Path::makeSafe($name, $stripChars), $expected, $message);
    }

    public function testRealpath(): void
    {
        $p = Path::realpath('foo/bar');

        self::assertEquals(Path::clean(getcwd() . '/foo/bar'), $p);
    }

    public function testBasename(): void
    {
        $file = '/var/www/foo/bar/yoo.inc.foo';

        self::assertEquals(
            'yoo.inc.foo',
            Path::basename($file)
        );

        self::assertEquals(
            'yoo.inc',
            Path::basename($file, true)
        );
        $file = '/var/www/中文路徑/目錄/檔案.inc.foo';

        self::assertEquals(
            '檔案.inc.foo',
            Path::basename($file)
        );

        self::assertEquals(
            '檔案.inc',
            Path::basename($file, true)
        );
    }

    /**
     * testIsChild
     *
     * @return  void
     */
    #[DataProvider('isChildProvider')]
    public function testIsChild(string $path, string $root, bool $isChild)
    {
        self::assertEquals(
            Path::isChild($path, $root),
            $isChild
        );
    }

    public static function isChildProvider(): array
    {
        return [
            'Windows' => [
                'C:\\foo\\bar\\yoo.inc.exe',
                'C:\\foo\\bar',
                true
            ],
            'Windows with trailing slash' => [
                'C:\\foo\\bar\\yoo.inc.exe',
                'C:\\foo\\bar\\',
                true
            ],
            'Windows not child' => [
                'C:\\foo\\bar\\yoo.inc.exe',
                'C:\\goo\\bar\\',
                false
            ],
            'Unix' => [
                '/var/foo/bar/yoo.inc.sh',
                '/var/foo',
                true
            ],
            'Unix with trailing slash' => [
                '/var/foo/bar/yoo.inc.sh',
                '/var/foo/',
                true
            ],
            'Unix not child' => [
                '/var/foo/bar/yoo.inc.sh',
                '/etc/foo/',
                false
            ],
            'Unix not child2' => [
                'foo/bar/yoo.inc.sh',
                '/var/foo/',
                false
            ],
        ];
    }

    public function testFindRoot(): void
    {
        self::assertPathEquals(
            '/',
            Path::findRoot('/foo/bar')
        );

        self::assertPathEquals(
            'C:/',
            Path::findRoot('C:/foo/bar')
        );

        self::assertPathEquals(
            'C:/',
            Path::findRoot('C:\\foo/bar')
        );

        $this->expectException(FilesystemException::class);

        self::assertPathEquals(
            'C:/',
            Path::findRoot('foo/bar')
        );
    }

    #[DataProvider('makeAbsoluteProvider')]
    public function testMakeAbsolute(string $path, string $base, string $expected)
    {
        if ($expected === FilesystemException::class) {
            $this->expectException(FilesystemException::class);
        }

        self::assertPathEquals(
            $expected,
            Path::makeAbsolute($path, $base)
        );
    }

    public static function makeAbsoluteProvider(): array
    {
        return [
            [
                'foo/bar',
                '/home/man',
                '/home/man/foo/bar'
            ],
            [
                'foo/bar',
                'C:\\Data',
                'C:/Data/foo/bar'
            ],
            [
                'C:/Hello/yoo/goz',
                'C:\\Data/foo',
                'C:/Hello/yoo/goz'
            ],
            // Same root
            [
                'C:/Hello/yoo/goz',
                'C:\\Data/foo',
                'C:/Hello/yoo/goz'
            ],
            [
                '/var/yoo/goz',
                '/Data/foo',
                '/var/yoo/goz'
            ],
            // Different root
            [
                '/Hello/yoo/goz',
                'C:\\Data/foo',
                FilesystemException::class
            ],
        ];
    }
}
