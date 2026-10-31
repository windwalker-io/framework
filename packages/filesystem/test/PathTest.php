<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\File;
use Windwalker\Filesystem\Path;

use function Windwalker\fs;

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

    #[Test]
    public function setPermissionsAppliesFileAndFolderModesRecursively(): void
    {
        $root = $this->createTempFolderPath();
        mkdir($root . '/sub', 0777, true);
        file_put_contents($root . '/a.txt', 'a');
        file_put_contents($root . '/sub/b.txt', 'b');

        try {
            self::assertTrue(Path::setPermissions($root, '0600', '0750'));

            self::assertSame('600', Path::getPermissions($root . '/a.txt'));
            self::assertSame('600', Path::getPermissions($root . '/sub/b.txt'));
            self::assertSame('750', Path::getPermissions($root . '/sub'));
            self::assertSame('750', Path::getPermissions($root));
        } finally {
            Path::setPermissions($root, '0644', '0755');
            fs($root)->delete();
        }
    }

    #[Test]
    public function setPermissionsOnSingleFileOnlyChangesFileMode(): void
    {
        $root = $this->createTempFolderPath();
        file_put_contents($root . '/a.txt', 'a');

        try {
            self::assertTrue(Path::setPermissions($root . '/a.txt', '0640'));
            self::assertSame('640', Path::getPermissions($root . '/a.txt'));
        } finally {
            fs($root)->delete();
        }
    }

    #[Test]
    public function setPermissionsReturnsFalseWhenChmodFails(): void
    {
        self::assertFalse(Path::setPermissions(sys_get_temp_dir() . '/windwalker-not-exists-' . uniqid()));
    }

    #[Test]
    public function getPermissionsReturnsOctalOrSymbolicString(): void
    {
        $root = $this->createTempFolderPath();
        file_put_contents($root . '/a.txt', 'a');
        chmod($root . '/a.txt', 0640);

        try {
            self::assertSame('640', Path::getPermissions($root . '/a.txt'));
            self::assertSame('rw-r-----', Path::getPermissions($root . '/a.txt', true));
        } finally {
            fs($root)->delete();
        }
    }

    #[Test]
    public function getPermissionsOfMissingPathAsStringReturnsPlaceholder(): void
    {
        self::assertSame(
            '---------',
            Path::getPermissions(sys_get_temp_dir() . '/windwalker-not-exists-' . uniqid(), true)
        );
    }

    #[Test]
    public function makeUtf8SafeKeepsUnicodeNamesAndStripsUnsafeCharacters(): void
    {
        self::assertSame('文件 name_1-2.txt', Path::makeUtf8Safe('文件 name_1-2.txt'));
        self::assertSame('abc.txt', Path::makeUtf8Safe('a<b>c.txt'));
        self::assertSame('filephp', Path::makeUtf8Safe('file..php'));
    }

    #[Test]
    public function makeSafeAbsoluteResolvesPathInsideBase(): void
    {
        self::assertPathEquals('/var/www/foo/bar.txt', Path::makeSafeAbsolute('foo/bar.txt', '/var/www'));
    }

    #[Test]
    public function makeSafeAbsoluteRejectsDotsByDefault(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessageIs('Invalid path.');

        Path::makeSafeAbsolute('../etc/passwd', '/var/www');
    }

    #[Test]
    public function makeSafeAbsoluteRejectsPathEscapingBaseWhenDotsAllowed(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessageIs('Invalid path.');

        Path::makeSafeAbsolute('../etc/passwd', '/var/www', true);
    }

    #[Test]
    public function makeSafeAbsoluteAllowsDotsThatStayInsideBase(): void
    {
        self::assertPathEquals('/var/www/b.txt', Path::makeSafeAbsolute('a/../b.txt', '/var/www', true));
    }

    #[Test]
    public function getHomeDirectoryReturnsHomeEnvironment(): void
    {
        $original = getenv('HOME');

        try {
            putenv('HOME=/tmp/windwalker-home');

            self::assertSame('/tmp/windwalker-home', Path::getHomeDirectory());
        } finally {
            $original === false ? putenv('HOME') : putenv('HOME=' . $original);
        }
    }

    #[Test]
    public function getHomeDirectoryThrowsWhenNoHomeCanBeFound(): void
    {
        $env = [];

        foreach (['HOME', 'HOMEDRIVE', 'HOMEPATH'] as $name) {
            $env[$name] = getenv($name);
            putenv($name);
        }

        try {
            $this->expectException(\RuntimeException::class);

            Path::getHomeDirectory();
        } finally {
            foreach ($env as $name => $value) {
                if ($value !== false) {
                    putenv($name . '=' . $value);
                }
            }
        }
    }

    private function createTempFolderPath(): string
    {
        $path = sys_get_temp_dir() . '/windwalker-path-test-' . uniqid();
        mkdir($path, 0755, true);

        return $path;
    }

    /**
     * Method to test clean().
     *
     * @param  string  $input
     * @param  string  $ds
     * @param  string  $expected
     *
     * @return  void
     *
     * @covers        \Windwalker\Filesystem\Path::clean
     */
    #[DataProvider('cleanProvider')]
    #[Test]
    public function cleanNormalizesSeparators(string $input, string $ds, string $expected): void
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
     * @return  void
     */
    #[DataProvider('existsProvider')]
    #[Test]
    public function existsChecksPathWithCaseSensitivity(string $path, bool $sExists, bool $iExists): void
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
    #[Test]
    public function fixCaseResolvesActualFilenameCase(): void
    {
        $path = __DIR__ . '/case/Flower/saKura/test.txt';

        self::assertEquals(Path::clean(__DIR__ . '/case/Flower/saKura/TEST.txt'), Path::fixCase($path));
    }

    #[Test]
    public function stripExtensionRemovesFileExtension(): void
    {
        $name = Path::stripExtension('Wu-la.la');

        $this->assertEquals('Wu-la', $name);

        $name = Path::stripExtension(__DIR__ . '/Wu-la.la');

        $this->assertEquals(__DIR__ . '/Wu-la', $name);
    }

    #[Test]
    public function getExtensionReturnsFileExtension(): void
    {
        $ext = Path::getExtension('Wu-la.la');

        $this->assertEquals('la', $ext);
    }

    #[Test]
    public function getFilenameReturnsNameWithoutDirectory(): void
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
     * @return  void
     */
    #[DataProvider('makeSafeProvider')]
    #[Test]
    public function makeSafeStripsUnsafeCharacters($name, $stripChars, $expected, $message): void
    {
        $this->assertEquals(Path::makeSafe($name, $stripChars), $expected, $message);
    }

    #[Test]
    public function realpathResolvesPath(): void
    {
        $p = Path::realpath('foo/bar');

        self::assertEquals(Path::clean(getcwd() . '/foo/bar'), $p);
    }

    #[Test]
    public function basenameReturnsLastSegment(): void
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
    #[Test]
    public function isChildDetectsPathUnderRoot(string $path, string $root, bool $isChild): void
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

    #[Test]
    public function findRootReturnsCommonParent(): void
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
    #[Test]
    public function makeAbsoluteResolvesRelativePath(string $path, string $base, string $expected): void
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
