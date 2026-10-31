<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Filesystem\FileObject;

use function Windwalker\fs;
use function Windwalker\glob;
use function Windwalker\glob_all;

/**
 * The FunctionsTest class.
 */
class FunctionsTest extends AbstractVfsTestCase
{
    use FilesystemTestTrait;

    #[Test]
    public function globMatchesDoubleStarPattern(): void
    {
        $files = glob('vfs://root/files/folder1/**/*');

        self::assertEquals(
            static::cleanPaths(
                [
                    'vfs://root/files/folder1/level2',
                    'vfs://root/files/folder1/level2/file3',
                    'vfs://root/files/folder1/path1',
                ]
            ),
            static::cleanPaths($files)
        );
    }

    #[Test]
    public function globReturnsEmptyArrayWhenNothingMatches(): void
    {
        self::assertSame([], glob('vfs://root/files/**/*.nothing'));
    }

    #[Test]
    public function globAllMergesMultiplePatterns(): void
    {
        $files = glob_all(
            [
                'vfs://root/files/folder1/**/*',
                'vfs://root/files/folder2/**/*',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                [
                    'vfs://root/files/folder1/level2',
                    'vfs://root/files/folder1/level2/file3',
                    'vfs://root/files/folder1/path1',
                    'vfs://root/files/folder2/file2.html',
                ]
            ),
            static::cleanPaths(array_map(strval(...), $files))
        );
    }

    #[Test]
    public function fsWrapsPathAsFileObject(): void
    {
        $file = fs('vfs://root/files/file1.txt');

        self::assertInstanceOf(FileObject::class, $file);
        self::assertPathEquals('vfs://root/files/file1.txt', $file->getPathname());
    }

    #[Test]
    public function fsAppliesRoot(): void
    {
        $file = fs('vfs://root/files/folder1/level2/file3', 'vfs://root/files');

        self::assertPathEquals('folder1/level2', $file->getRelativePath());
    }
}
