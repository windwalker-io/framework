<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem\Test\Iterator;

use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Filesystem\Test\AbstractVfsTestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;

use function Windwalker\regex;

/**
 * The FilesIteratorTest class.
 */
class FilesIteratorTestVfsCase extends AbstractVfsTestCase
{
    use BaseAssertionTrait;

    /**
     * Property dest.
     *
     * @var string
     */
    protected static $dest = __DIR__ . '/../dest';

    /**
     * Property src.
     *
     * @var string
     */
    protected static $src = __DIR__ . '/../files';

    /**
     * @var FilesIterator
     */
    protected $instance;

    /**
     * @see  FilesIterator::getInnerIterator
     */
    public function testIter(): void
    {
        $it = FilesIterator::create(static::$baseDir, true);

        self::assertEquals(
            static::cleanPaths(static::getItemsRecursive()),
            static::cleanPaths($it->toArray())
        );
    }

    public function testFirst()
    {
        $it = FilesIterator::create(static::$baseDir, true);

        self::assertEquals(
            'vfs://root/files/folder1',
            $it->current()->getPathname()
        );
    }

    /**
     * @see  FilesIterator::map
     */
    public function testMap(): void
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
    public function testFilter(): void
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
            ->filter(regex('folder2'))
            ->map(
                static function (FileObject $file) {
                    return $file->getFilename();
                }
            );

        self::assertEquals(
            ['folder2'],
            $it->toArray()
        );
    }
}
