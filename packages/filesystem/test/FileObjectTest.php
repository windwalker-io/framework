<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Test;
use SplFileInfo;
use Symfony\Component\Mime\MimeTypes;
use Windwalker\Filesystem\Exception\FileNotFoundException;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Filesystem\Path;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Stream\StringStream;

use function Windwalker\fs;

/**
 * The FileObjectTest class.
 */
class FileObjectTest extends AbstractVfsTestCase
{
    use FilesystemTestTrait;

    #[Test]
    public function filesRecursiveFirstReturnsDeepestFirstFile(): void
    {
        $fs = new FileObject('vfs://root/files');
        $files = $fs->files(true);

        self::assertPathEquals('vfs://root/files/folder1/level2/file3', $files->first()->getPathname());
    }

    #[Test]
    public function readReturnsFileContents(): void
    {
        $str = (new FileObject('vfs://root/files/folder2/file2.html'))->read();

        self::assertEquals('file2.html', (string) $str);
    }

    #[Test]
    public function readStreamReturnsStreamOfFileContents(): void
    {
        $stream = (new FileObject('vfs://root/files/folder2/file2.html'))->readStream();

        self::assertEquals('file2.html', (string) $stream);
    }

    #[Test]
    public function writeCreatesMissingParentDirectoriesAndFile(): void
    {
        (new FileObject('vfs://root/files/folder2/foo/bar/new-file.txt'))->write('Hello');

        self::assertEquals('Hello', file_get_contents('vfs://root/files/folder2/foo/bar/new-file.txt'));
    }

    #[Test]
    public function writeStreamWritesStreamContentsToFile(): void
    {
        $stream = new StringStream('Hello');

        (new FileObject('vfs://root/files/folder2/foo/bar/new-file.txt'))->writeStream($stream);

        self::assertEquals('Hello', file_get_contents('vfs://root/files/folder2/foo/bar/new-file.txt'));
    }

    #[Test]
    public function deleteRemovesDirectoryRecursively(): void
    {
        $file = new FileObject(static::$baseDir);
        $file->delete();

        self::assertDirectoryDoesNotExist(static::$baseDir);
        self::assertFileDoesNotExist(static::$baseDir . '/folder1/level2/file3');
    }

    #[Test]
    public function deleteNonExistentPathDoesNotThrowUnexpectedException(): void
    {
        try {
            (new FileObject(static::$baseDir . '/hello/no/exists'))->delete();
        } catch (FilesystemException $e) {
            self::assertInstanceOf(FilesystemException::class, $e);
        }

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function deleteDirectoryWithoutPermissionThrowsFilesystemExceptionOrSucceeds(): void
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('File permissions are not supported on Windows.');
        }

        $dir = new FileObject(__DIR__ . '/dest');
        $dir->mkdir();
        chmod($dir->getPathname(), 0000);

        try {
            $dir->delete();
        } catch (FilesystemException $e) {
            self::assertInstanceOf(FilesystemException::class, $e);
        }

        chmod($dir->getPathname(), 0777);
        $dir->delete();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function mkdirCreatesNestedDirectories(): void
    {
        $dest = fs('vfs://root/dest/foo/bar');

        $dest->mkdir();

        static::assertDirectoryExists('vfs://root/dest/foo/bar');
    }

    #[Test]
    public function copyToCopiesDirectoryRecursively(): void
    {
        $dest = new FileObject('vfs://root/dest');

        if ($dest->isDir()) {
            $dest->delete();
        }

        (new FileObject(static::$baseDir))->copyTo($dest);

        $this->assertDirectoryExists($dest->getPathname());
        $this->assertFileExists($dest . '/folder1/level2/file3');
    }

    #[Test]
    public function moveToMovesDirectoryRecursively(): void
    {
        $dest = new FileObject('vfs://root/files2');

        if ($dest->isDir()) {
            $dest->delete();
        }

        (new FileObject(static::$baseDir))->moveTo($dest);

        $this->assertDirectoryExists($dest->getPathname());
        $this->assertFileExists($dest . '/folder1/level2/file3');

        $dest->delete();
    }

    #[Test]
    public function itemsRecursiveListsNestedFolderItems(): void
    {
        $items = (new FileObject(static::$baseDir . '/folder1/level2'))->items(true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2/file3']),
            static::cleanPaths($items)
        );
    }

    #[Test]
    public function itemsRecursiveListsAllFilesAndFolders(): void
    {
        $items = (new FileObject(static::$baseDir))->items(true);

        $this->assertEquals(
            static::cleanPaths(static::getItemsRecursive()),
            static::cleanPaths($items)
        );
    }

    #[Test]
    public function itemsReturnsRewindableFilesIterator(): void
    {
        $items = (new FileObject(static::$baseDir))->items(true);

        $this->assertInstanceOf(FilesIterator::class, $items);

        $this->assertEquals(
            static::cleanPaths(static::getItemsRecursive()),
            static::cleanPaths($items->toArray())
        );

        $items->rewind();

        $this->assertInstanceOf(SplFileInfo::class, $items->current());
    }

    #[Test]
    public function itemsOnNonExistentFolderThrowsFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new FileObject(__DIR__ . '/not/exists'))->items();
    }

    #[Test]
    public function foldersRecursiveListsNestedFolders(): void
    {
        $folders = (new FileObject(static::$baseDir . '/folder1'))->folders(true);

        $this->assertEquals(
            static::cleanPaths([static::$baseDir . '/folder1/level2']),
            static::cleanPaths($folders)
        );
    }

    #[Test]
    public function foldersRecursiveListsAllFolders(): void
    {
        $folders = (new FileObject(static::$baseDir))->folders(true);

        $this->assertEquals(
            static::cleanPaths(static::getFoldersRecursive()),
            static::cleanPaths($folders)
        );
    }

    #[Test]
    public function foldersReturnsFilesIteratorOfFileObjects(): void
    {
        $folders = (new FileObject(static::$baseDir))->folders(true);

        $this->assertInstanceOf(FilesIterator::class, $folders);

        $this->assertEquals(
            static::cleanPaths(static::getFoldersRecursive()),
            static::cleanPaths($folders->toArray())
        );

        $folders = (new FileObject(static::$baseDir))->folders(true);

        $this->assertInstanceOf(FileObject::class, $folders->current());
    }

    #[Test]
    public function filesRecursiveListsNestedFolderFiles(): void
    {
        $files = (new FileObject('vfs://root/files/folder1/level2'))->files(true);

        $this->assertEquals(
            static::cleanPaths(['vfs://root/files/folder1/level2/file3']),
            static::cleanPaths($files)
        );
    }

    #[Test]
    public function filesRecursiveListsAllFiles(): void
    {
        $files = (new FileObject(static::$baseDir))->files(true);

        $this->assertEquals(
            static::cleanPaths(static::getFilesRecursive()),
            static::cleanPaths($files)
        );
    }

    #[Test]
    public function filesReturnsRewindableFilesIteratorOfFileObjects(): void
    {
        $files = (new FileObject(static::$baseDir))->files(true);

        $this->assertInstanceOf(FilesIterator::class, $files);

        $this->assertEquals(
            static::cleanPaths(static::getFilesRecursive()),
            static::cleanPaths($files->toArray())
        );

        $files->rewind();

        $this->assertInstanceOf(FileObject::class, $files->current());
    }

    #[Test]
    public function wrapIfNotNullReturnsNullForNull(): void
    {
        self::assertNull(FileObject::wrapIfNotNull(null));
    }

    #[Test]
    public function getRelativePathnameUsesBasePathFromConstructor(): void
    {
        $file = fs('vfs://root/files\\folder1/foo/bar', 'vfs://root\\files');

        self::assertPathEquals('folder1/foo/bar', $file->getRelativePathname());
        self::assertPathEquals('foo/bar', $file->getRelativePathname('vfs://root\\files/folder1'));
    }

    #[Test]
    public function getRelativePathnameWithoutBasePathResolvesAgainstGivenPath(): void
    {
        $file = fs('vfs://root/files\\folder1/foo/bar', '');

        self::assertPathEquals('vfs://root/files/folder1/foo/bar', $file->getRelativePathname());
        self::assertPathEquals(
            'bar',
            $file->getRelativePathname('vfs://root/files\\folder1/foo')
        );
        self::assertPathEquals(
            '../bar',
            $file->getRelativePathname('vfs://root/files\\folder1/foo/yoo')
        );
    }

    #[Test]
    public function appendPathAppendsSegmentToPathname(): void
    {
        $file = fs('vfs://root')->appendPath('/files/folder1');

        self::assertPathEquals('vfs://root\\files/folder1', $file->getPathname());
    }

    #[Test]
    public function prependPathPrependsSegmentToPathname(): void
    {
        $file = fs('root/files/folder1')->prependPath('vfs://');

        self::assertPathEquals('vfs://root\\files/folder1', $file->getPathname());
    }

    #[Test]
    public function getExtensionReturnsFileExtension(): void
    {
        self::assertSame('html', fs('vfs://root/files/folder2/file2.html')->getExtension());
    }

    #[Test]
    public function getPathObjectReturnsParentFolderObject(): void
    {
        $dir = fs('vfs://root/files/folder2/file2.html')->getPathObject();

        self::assertPathEquals('vfs://root/files/folder2', $dir->getPathname());
    }

    #[Test]
    public function readBase64DataUriEncodesContentsWithGivenMime(): void
    {
        $uri = fs('vfs://root/files/folder2/file2.html')->readBase64DataUri('text/html');

        self::assertSame('data:text/html;base64,' . base64_encode('file2.html'), $uri);
    }

    #[Test]
    public function readBase64DataUriDetectsMimeFromMimeTypes(): void
    {
        $uri = fs('vfs://root/files/folder2/file2.html')->readBase64DataUri(new MimeTypes());

        self::assertSame('data:text/html;base64,' . base64_encode('file2.html'), $uri);
    }

    #[Test]
    public function readBase64DataUriThrowsWhenMimeCannotBeDetected(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessageIs('Unable to detect file mime type');

        fs('vfs://root/files/folder1/path1')->readBase64DataUri(new MimeTypes());
    }

    #[Test]
    public function readAndParseParsesFileByExtension(): void
    {
        file_put_contents('vfs://root/files/data.json', '{"foo":"bar"}');

        $data = fs('vfs://root/files/data.json')->readAndParse();

        self::assertSame('bar', $data['foo']);
    }

    #[Test]
    public function addFileWritesFileIntoFolder(): void
    {
        $file = fs('vfs://root/files/folder2')->addFile('new.txt', 'Hello');

        self::assertPathEquals('vfs://root/files/folder2/new.txt', $file->getPathname());
        self::assertSame('Hello', file_get_contents('vfs://root/files/folder2/new.txt'));
    }

    #[Test]
    public function addFileThrowsWhenCurrentPathIsNotFolder(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessageIsOrContains('current path is not a dir');

        fs('vfs://root/files/folder2/file2.html')->addFile('new.txt');
    }

    #[Test]
    public function deleteIfExistsDeletesExistingFileAndReturnsTrue(): void
    {
        self::assertTrue(fs('vfs://root/files/folder2/file2.html')->deleteIfExists());
        self::assertFileDoesNotExist('vfs://root/files/folder2/file2.html');
    }

    #[Test]
    public function deleteIfExistsReturnsFalseForMissingPath(): void
    {
        self::assertFalse(fs('vfs://root/files/not-exists.txt')->deleteIfExists());
    }

    #[Test]
    public function setRootUnwrapsFileObjectAndGetRootReturnsIt(): void
    {
        $file = fs('vfs://root/files/folder1/path1');

        $file->setRoot(new SplFileInfo('vfs://root/files'));

        self::assertSame('vfs://root/files', $file->getRoot());
        self::assertPathEquals('folder1/path1', $file->getRelativePathname());

        $file->setRoot(null);

        self::assertNull($file->getRoot());
    }

    #[Test]
    public function isLinkDetectsSymbolicLink(): void
    {
        $dir = sys_get_temp_dir() . '/windwalker-link-test-' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/target.txt', 'x');

        try {
            if (defined('PHP_WINDOWS_VERSION_BUILD')) {
                $created = @symlink($dir . '/target.txt', $dir . '/link.txt');

                if ($created) {
                    self::assertTrue(fs($dir . '/link.txt')->isLink());
                } else {
                    self::assertFalse(is_link($dir . '/link.txt'));
                    self::assertFileDoesNotExist($dir . '/link.txt');
                }
            } else {
                self::assertTrue(symlink($dir . '/target.txt', $dir . '/link.txt'));
                self::assertTrue(fs($dir . '/link.txt')->isLink());
            }

            self::assertFalse(fs($dir . '/target.txt')->isLink());
        } finally {
            if (is_link($dir . '/link.txt')) {
                unlink($dir . '/link.txt');
            }

            unlink($dir . '/target.txt');
            rmdir($dir);
        }
    }

    #[Test]
    public function junctionHelpersAreNoOpOnNonWindows(): void
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            self::markTestSkipped('Junctions are real on Windows.');
        }

        $file = fs('vfs://root/files/folder1');

        self::assertFalse($file->isJunction());
        self::assertFalse($file->removeJunction());
    }

    #[Test]
    public function isLinkRejectsOrdinaryFilesAndMissingPaths(): void
    {
        self::assertFalse(fs(__FILE__)->isLink());
        self::assertFalse(fs(Path::clean(__FILE__, '/'))->isLink());
        self::assertFalse(fs(Path::relative(getcwd(), __FILE__))->isLink());
        self::assertFalse(fs(__DIR__ . '/missing-link-' . uniqid('', true))->isLink());
    }

    #[Test]
    public function isLinkRecognizesWindowsJunction(): void
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            self::markTestSkipped('Junctions are only supported on Windows.');
        }

        $dir = sys_get_temp_dir() . '/windwalker-junction-test-' . uniqid('', true);
        mkdir($dir);
        mkdir($dir . '/target');
        file_put_contents($dir . '/target/file.txt', 'x');

        try {
            self::assertTrue(Filesystem::symlink($dir . '/target', $dir . '/link'));
            self::assertTrue(fs($dir . '/link')->isLink());
            self::assertTrue(fs($dir . '/link')->isJunction());
            self::assertFalse(fs($dir . '/target')->isLink());
            self::assertSame('x', file_get_contents($dir . '/link/file.txt'));
            self::assertTrue(fs($dir . '/link')->removeJunction());
            self::assertFileExists($dir . '/target/file.txt');
        } finally {
            if (fs($dir . '/link')->isJunction()) {
                rmdir($dir . '/link');
            }

            unlink($dir . '/target/file.txt');
            rmdir($dir . '/target');
            rmdir($dir);
        }
    }

    #[Test]
    public function callRunsAllowedMethodAsync(): void
    {
        $file = fs('vfs://root/files/file1.txt');

        self::assertSame('file1.txt', (string) $file->readAsync()->wait());
    }

    #[Test]
    public function callRunsWriteAsyncWithArguments(): void
    {
        $file = fs('vfs://root/files/async.txt');

        $file->writeAsync('async data')->wait();

        self::assertSame('async data', (string) $file->read());
    }

    #[Test]
    public function callRejectsWhenAsyncMethodFails(): void
    {
        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessageIsOrContains('Try to read from a non-exists file');

        fs('vfs://root/files/not-exists.txt')->readAsync()->wait();
    }

    #[Test]
    public function callThrowsForUnknownMethod(): void
    {
        $file = fs('vfs://root/files/file1.txt');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('::fooAsync not exists.');

        $file->fooAsync();
    }

    #[Test]
    public function callThrowsForDisallowedAsyncMethod(): void
    {
        $this->expectException(BadMethodCallException::class);

        fs('vfs://root/files/file1.txt')->getPathnameAsync();
    }

    #[Test]
    public function deleteNestedDirectories(): void
    {
        $root = sys_get_temp_dir() . '/ww-del-' . uniqid();
        mkdir($root . '/a/b/c', 0777, true);
        touch($root . '/a/b/c/f.txt');
        touch($root . '/a/x.txt');

        self::assertTrue(fs($root)->delete());
        self::assertDirectoryDoesNotExist($root);
    }

    #[Test]
    public function deleteWithoutFollowLinksKeepsTarget(): void
    {
        $root = sys_get_temp_dir() . '/ww-del-' . uniqid();
        mkdir($root . '/target', 0777, true);
        touch($root . '/target/keep.txt');
        symlink($root . '/target', $root . '/link');

        fs($root . '/link')->delete(followLinks: false);

        self::assertFalse(is_link($root . '/link'));
        self::assertFileExists($root . '/target/keep.txt');

        fs($root)->delete();
    }
}
