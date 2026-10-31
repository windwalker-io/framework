<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use PHPUnit\Framework\Attributes\Test;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\PathCollection;

use function Windwalker\fs;

/**
 * The PathCollectionTest class.
 */
class PathCollectionTest extends AbstractVfsTestCase
{
    /**
     * @see  PathCollection::isChild
     */
    #[Test]
    public function isChildDetectsPathInCollection(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
                'vfs://foo/goo',
            ]
        );

        self::assertTrue($p->isChild('vfs://foo/goo/joo'));
        self::assertFalse($p->isChild('vfs://foo/tu'));
    }

    /**
     * @see  PathCollection::addPaths
     */
    #[Test]
    public function addPathsAppendsMultiplePaths(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->addPaths(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ]
        );

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::add
     */
    #[Test]
    public function addAppendsSinglePath(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->add('vfs://flower/sakura');

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::appendAll
     */
    #[Test]
    public function appendAllAddsSuffixToEveryPath(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        $p2 = $p->appendAll('/../');

        self::assertEquals(
            [
                'foo/bar/yoo/..',
                'flower/sakura/..',
                'foo/goo/joo/..',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::prependAll
     */
    #[Test]
    public function prependAllAddsPrefixToEveryPath(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        $p2 = $p->prependAll('vfs://');

        self::assertEquals(
            [
                'vfs://foo/bar/yoo',
                'vfs://flower/sakura',
                'vfs://foo/goo/joo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::getPaths
     */
    #[Test]
    public function getPathsReturnsAllPaths(): void
    {
        $p = new PathCollection(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ]
        );

        self::assertEquals(
            [
                'foo/bar/yoo',
                'flower/sakura',
                'foo/goo/joo',
            ],
            array_map('strval', $p->getPaths())
        );
    }

    /**
     * @see  PathCollection::withPaths
     */
    #[Test]
    public function withPathsReturnsNewInstance(): void
    {
        $p = new PathCollection(
            [
                'vfs://foo/bar/yoo',
            ]
        );

        $p2 = $p->withPaths(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ]
        );

        self::assertEquals(
            [
                'vfs://flower/sakura',
                'vfs://foo/goo',
            ],
            $p2->toArray()
        );
        self::assertNotSame($p, $p2);
    }

    /**
     * @see  PathCollection::items
     */
    #[Test]
    public function itemsListsAllChildren(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->items(true)->toArray(),
                    fs('vfs://root/folder2')->items(true)->toArray()
                )
            ),
            static::cleanPaths($p->items(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::files
     */
    #[Test]
    public function filesListsOnlyFiles(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->files(true)->toArray(),
                    fs('vfs://root/folder2')->files(true)->toArray()
                )
            ),
            static::cleanPaths($p->files(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::folders
     */
    #[Test]
    public function foldersListsOnlyFolders(): void
    {
        $this->setUpNestedFiles();

        $p = new PathCollection(
            [
                'vfs://root/folder1',
                'vfs://root/folder2',
            ]
        );

        self::assertEquals(
            static::cleanPaths(
                array_merge(
                    fs('vfs://root/folder1')->folders(true)->toArray(),
                    fs('vfs://root/folder2')->folders(true)->toArray()
                )
            ),
            static::cleanPaths($p->folders(true)->toArray())
        );
    }

    /**
     * @see  PathCollection::getPath
     */
    #[Test]
    public function getPathReturnsPathAtIndex(): void
    {
        $p = new PathCollection(['vfs://foo/bar', 'vfs://foo/goo']);

        self::assertPathEquals('vfs://foo/goo', $p->getPath(1)->getPathname());
        self::assertNull($p->getPath(5));
    }

    /**
     * @see  PathCollection::map
     */
    #[Test]
    public function mapTransformsPaths(): void
    {
        $p = new PathCollection(['vfs://foo/bar', 'vfs://foo/goo']);

        $p2 = $p->map(fn(FileObject $file) => $file->appendPath('/sub'));

        self::assertNotSame($p, $p2);
        self::assertPathEquals('vfs://foo/bar/sub', $p2->getPath(0)->getPathname());
        self::assertPathEquals('vfs://foo/goo/sub', $p2->getPath(1)->getPathname());
        self::assertPathEquals('vfs://foo/bar', $p->getPath(0)->getPathname());
    }

    protected function setUpNestedFiles(): vfsStreamDirectory
    {
        return vfsStream::setup(
            'root',
            null,
            [
                'folder1' => [
                    'sub1' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                    'sub2' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                ],
                'folder2' => [
                    'sub1' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                    'sub2' => [
                        'file1.txt' => '',
                        'file2.txt' => '',
                        'file3.txt' => '',
                    ],
                ],
            ]
        );
    }
}
