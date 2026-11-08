<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;
use Windwalker\Filesystem\Exception\FileNotFoundException;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Iterator\FilesIterator;

/**
 * The FilesystemTest class.
 */
class FilesystemTest extends AbstractVfsTestCase
{
    use FilesystemTestTrait;

    /**
     * @var  Filesystem
     */
    protected $instance;

    #[Test]
    public function itemsFirstReturnsFirstChild(): void
    {
        $fs = new Filesystem();
        $files = $fs::files('vfs://root/files', true);

        self::assertPathEquals('vfs://root/files/folder1/level2/file3', $files->first()->getPathname());
    }

    /**
     * @see  Filesystem::get
     */
    #[Test]
    public function getReturnsFileObject(): void
    {
        $fs = new Filesystem();
        $file = $fs::get('vfs://root/files/folder2/file2.html');

        self::assertEquals('file2.html', (string) $file->read());
    }

    /**
     * testRead
     *
     * @return  void
     */
    #[Test]
    public function readReturnsFileContents(): void
    {
        $fs = new Filesystem();
        $str = $fs::read('vfs://root/files/folder2/file2.html');

        self::assertEquals('file2.html', (string) $str);
    }

    #[Test]
    public function readAndParseReadsAndParsesFile(): void
    {
        file_put_contents('vfs://root/files/data.json', '{"foo":"bar"}');

        $data = Filesystem::readAndParse('vfs://root/files/data.json');

        self::assertSame('bar', $data['foo']);
    }

    #[Test]
    public function createTempAsyncIsNotSupported(): void
    {
        $this->expectException(BadMethodCallException::class);

        Filesystem::createTempAsync();
    }

    /**
     * @see  Filesystem::delete
     */
    #[Test]
    public function deleteRemovesFilesAndFolders(): void
    {
        $fs = new Filesystem();

        $fs::delete(static::$baseDir . '');

        $this->assertDirectoryDoesNotExist(static::$baseDir . '');
        $this->assertFileDoesNotExist(static::$baseDir . '/folder1/level2/file3');

        // Delete non-exists folders
        try {
            $fs::delete(static::$baseDir . '/hello/no/exists');
        } catch (FilesystemException $e) {
            self::assertInstanceOf(FilesystemException::class, $e);
        }

        // Delete no-permissions folders
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $dir = __DIR__ . '/dest';
            $fs::mkdir($dir);
            chmod($dir, 0000);

            try {
                $fs::delete($dir);
            } catch (FilesystemException $e) {
                self::assertInstanceOf(FilesystemException::class, $e);
            }

            chmod($dir, 0777);
            $fs::delete($dir);
        }
    }

    /**
     * @see  Filesystem::mkdir
     */
    #[Test]
    public function mkdirCreatesFolder(): void
    {
        $fs = new Filesystem();
        $dest = 'vfs://root/dest';

        $fs::mkdir($dest);

        static::assertDirectoryExists('vfs://root/dest');
    }

    /**
     * @see  Filesystem::copy
     */
    #[Test]
    public function copyDuplicatesFileOrFolder(): void
    {
        $fs = new Filesystem();

        $dest = 'vfs://root/dest';

        if (is_dir($dest)) {
            $fs::delete($dest);
        }

        $fs::copy(static::$baseDir, $dest);

        $this->assertDirectoryExists($dest);
        $this->assertFileExists($dest . '/folder1/level2/file3');
    }

    /**
     * @see  Filesystem::move
     */
    #[Test]
    public function moveRelocatesFileOrFolder(): void
    {
        $fs = new Filesystem();

        $dest = 'vfs://root/files2';

        if (is_dir($dest)) {
            $fs::delete($dest);
        }

        $fs::move(static::$baseDir, $dest);

        $this->assertDirectoryExists($dest);
        $this->assertFileExists($dest . '/folder1/level2/file3');

        $fs::delete($dest);
    }

    /**
     * @see  Filesystem::items
     */
    #[Test]
    public function itemsListsChildren(): void
    {
        $fs = new Filesystem();

        $items = $fs::items(static::$baseDir . '/folder1/level2', true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2/file3']),
            static::cleanPaths($items)
        );

        // Recursive
        $items = $fs::items(static::$baseDir . '', true);

        $compare = static::getItemsRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items)
        );

        // Iterator
        $items = $fs::items(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $items);

        $items2 = Filesystem::toArray($items);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($items2)
        );

        $items->rewind();

        $this->assertInstanceOf(SplFileInfo::class, $items->current());

        // list non-exists folder
        $this->expectException(FileNotFoundException::class);

        $items = $fs::items(__DIR__ . '/not/exists');
    }

    /**
     * @see  Filesystem::glob
     */
    #[Test]
    public function globMatchesPattern(): void
    {
        $fs = new Filesystem();
        $files = $fs::glob('vfs://root/**/*');

        static::assertEquals(
            static::cleanPaths($fs::items('vfs://root', true)),
            static::cleanPaths($files->toArray())
        );
    }

    /**
     * @see  Filesystem::globAll
     */
    #[Test]
    public function globAllMatchesPatternRecursively(): void
    {
        $fs = new Filesystem();

        $iter = $fs::globAll(
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
            static::cleanPaths(
                $iter->toArray()
            )
        );
    }

    /**
     * @see  Filesystem::folders
     */
    #[Test]
    public function foldersListsOnlyFolders(): void
    {
        $fs = new Filesystem();

        $folders = $fs::folders(static::$baseDir . '/folder1', true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2']),
            static::cleanPaths($folders)
        );

        // Recursive
        $folders = $fs::folders(static::$baseDir, true);

        $compare = static::getFoldersRecursive('dest');

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders)
        );

        // Iterator
        $folders = $fs::folders(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $folders);

        $folders2 = Filesystem::toArray($folders);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($folders2)
        );

        $folders = $fs::folders(static::$baseDir, true);

        $this->assertInstanceOf(FileObject::class, $folders->current());
    }

    /**
     * @see  Filesystem::files
     */
    #[Test]
    public function filesListsOnlyFiles(): void
    {
        $fs = new Filesystem();

        $files = $fs::files('vfs://root/files/folder1/level2', true);

        $this->assertEquals(
            static::cleanPaths(['vfs://root/files/folder1/level2/file3']),
            static::cleanPaths($files)
        );

        // Recursive
        $files = $fs::files(static::$baseDir, true);

        $compare = static::getFilesRecursive();

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files)
        );

        // Iterator
        $files = $fs::files(static::$baseDir, true);

        $this->assertInstanceOf(FilesIterator::class, $files);

        $files2 = Filesystem::toArray($files);

        $this->assertEquals(
            static::cleanPaths($compare),
            static::cleanPaths($files2)
        );

        $files->rewind();

        $this->assertInstanceOf(FileObject::class, $files->current());
    }

    #[Test]
    public function createTempCreatesFileAndDeletesItWhenDestructed(): void
    {
        $temp = Filesystem::createTemp(null, 'ww-test-');

        $path = $temp->getPathname();

        self::assertFileExists($path);

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            // Windows tempnam() uses only the first three prefix characters.
            self::assertStringStartsWith('ww-', basename($path));
        } else {
            self::assertStringStartsWith('ww-test-', basename($path));
        }

        unset($temp);

        self::assertFileDoesNotExist($path);
    }

    #[Test]
    public function createTempWithoutFlagsKeepsFileAfterDestruct(): void
    {
        $temp = Filesystem::createTemp(null, null, 0);
        $path = $temp->getPathname();

        unset($temp);

        self::assertFileExists($path);

        unlink($path);
    }

    #[Test]
    public function createTempCreatesMissingDirectory(): void
    {
        $dir = sys_get_temp_dir() . '/windwalker-temp-dir-' . uniqid();

        $temp = Filesystem::createTemp($dir);

        self::assertDirectoryExists($dir);
        self::assertSame(realpath($dir), realpath(dirname($temp->getPathname())));

        unset($temp);

        rmdir($dir);
    }

    #[Test]
    public function createTempFolderCreatesFolderAndDeletesItWhenDestructed(): void
    {
        $temp = Filesystem::createTempFolder(null, 'ww-folder-');

        $path = $temp->getPathname();

        self::assertDirectoryExists($path);
        self::assertStringContainsString('ww-folder-', basename($path));

        unset($temp);

        self::assertDirectoryDoesNotExist($path);
    }

    #[Test]
    public function symlinkCreatesLinkToTarget(): void
    {
        $dir = sys_get_temp_dir() . '/windwalker-symlink-test-' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/target.txt', 'x');

        try {
            if (defined('PHP_WINDOWS_VERSION_BUILD')) {
                // Probe privileges independently so implementation failures are not mistaken for permission limits.
                $canCreateLink = @symlink($dir . '/target.txt', $dir . '/probe.txt');

                if ($canCreateLink) {
                    self::assertTrue(is_link($dir . '/probe.txt'));
                    unlink($dir . '/probe.txt');
                    self::assertTrue(Filesystem::symlink($dir . '/target.txt', $dir . '/link.txt'));
                    self::assertTrue(is_link($dir . '/link.txt'));
                } else {
                    self::assertFalse(Filesystem::symlink($dir . '/target.txt', $dir . '/link.txt'));
                    self::assertFalse(is_link($dir . '/link.txt'));
                    self::assertFileDoesNotExist($dir . '/link.txt');
                }
            } else {
                self::assertTrue(Filesystem::symlink($dir . '/target.txt', $dir . '/link.txt'));
                self::assertTrue(is_link($dir . '/link.txt'));
            }
        } finally {
            @unlink($dir . '/link.txt');

            if (is_link($dir . '/probe.txt')) {
                unlink($dir . '/probe.txt');
            }

            unlink($dir . '/target.txt');
            rmdir($dir);
        }
    }

    #[Test]
    public function junctionHelpersReturnFalseOnNonWindows(): void
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            self::markTestSkipped('Junctions are real on Windows.');
        }

        $fs = new Filesystem();

        self::assertFalse($fs->isJunction('vfs://root/files/folder1'));
        self::assertFalse($fs->removeJunction('vfs://root/files/folder1'));
    }
}
