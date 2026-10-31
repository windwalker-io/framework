<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test\Iterator;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Filesystem\Test\AbstractVfsTestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The FilesIteratorTest class.
 */
class FilesIteratorTest extends AbstractVfsTestCase
{
    use BaseAssertionTrait;

    /**
     * Property dest.
     *
     * @var  string
     */
    protected static $dest = __DIR__ . '/../dest';

    /**
     * Property src.
     *
     * @var  string
     */
    protected static $src = __DIR__ . '/../files';

    /**
     * @see  FilesIterator::getInnerIterator
     */
    #[Test]
    public function iteratesAllItemsRecursively(): void
    {
        $it = FilesIterator::create(static::$baseDir, true);

        self::assertEquals(
            static::cleanPaths(static::getItemsRecursive()),
            static::cleanPaths($it->toArray())
        );
    }

    #[Test]
    public function firstReturnsFirstItem(): void
    {
        $it = FilesIterator::create(static::$baseDir, true);

        self::assertPathEquals(
            'vfs://root/files/folder1',
            $it->current()->getPathname()
        );
    }

    /**
     * @see  FilesIterator::map
     */
    #[Test]
    public function mapTransformsItems(): void
    {
        $it = FilesIterator::create('vfs://root/files');

        $it = $it->map(
            static function (FileObject $file) {
                return $file->getFilename();
            }
        );

        self::assertArraySimilar(
            ['file1.txt', 'folder2', 'folder1'],
            $it->toArray()
        );
    }

    /**
     * @see  FilesIterator::filter
     */
    #[Test]
    public function filterKeepsMatchingItems(): void
    {
        $it = FilesIterator::create('vfs://root/files');

        $it = $it
            ->filter(
                static function (FileObject $file) {
                    return $file->isDir();
                }
            )
            ->map(
                static function (FileObject $file) {
                    return $file->getFilename();
                }
            );

        self::assertArraySimilar(
            ['folder2', 'folder1'],
            $it->toArray()
        );

        $it = FilesIterator::create('vfs://root/files');

        $it = $it
            ->filter(
                static fn(FileObject $v) => str_contains($v->getPathname(), 'folder2')
            )
            ->map(
                static fn(FileObject $file) => $file->getFilename()
            );

        self::assertEquals(
            ['folder2'],
            $it->toArray()
        );
    }

    #[Test]
    public function observeCallsCallbackForEachItem(): void
    {
        $ob = FilesIterator::observable('vfs://root/files');

        $values = [];

        $ob
            ->filter(
                static function (FileObject $file) {
                    return $file->isDir();
                }
            )
            ->map(
                static function (FileObject $file) {
                    return $file->getFilename();
                }
            )
            ->subscribe(
                function ($v) use (&$values) {
                    $values[] = $v;
                }
            );

        self::assertArraySimilar(
            ['folder2', 'folder1'],
            $values
        );

        $ob = FilesIterator::observable('vfs://root/files');

        $values = [];

        $ob = $ob
            ->filter(
                static fn(FileObject $file) => str_contains($file->getPathname(), 'folder2')
            )
            ->map(
                static fn(FileObject $file) => $file->getFilename()
            )
            ->subscribe(
                function ($v) use (&$values) {
                    $values[] = $v;
                }
            );

        self::assertEquals(
            ['folder2'],
            $values
        );
    }
}
