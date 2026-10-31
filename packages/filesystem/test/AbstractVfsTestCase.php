<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use org\bovigo\vfs\visitor\vfsStreamVisitor;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Path;

/**
 * The AbstractFilesystemTest class.
 *
 * @since  2.0
 */
abstract class AbstractVfsTestCase extends TestCase
{
    use FilesystemTestTrait;

    /**
     * @var  array
     */
    protected static array $structure = [
        'files' => [
            'folder1' => [
                'level2' => [
                    'file3' => 'file3',
                ],
                'path1' => 'path1',
            ],
            'folder2' => [
                'file2.html' => 'file2.html',
            ],
            'file1.txt' => 'file1.txt',
        ],
    ];

    /**
     * Property src.
     *
     * @var  string
     */
    protected static string $baseDir = 'vfs://root/files';

    /**
     * @var  ?vfsStreamDirectory
     */
    protected ?vfsStreamDirectory $root = null;

    #[Before]
    protected function createVfsRoot(): void
    {
        $this->root = vfsStream::setup('root', null, static::$structure);
    }

    protected function inspectVfs(): vfsStreamVisitor
    {
        return vfsStream::inspect(new vfsStreamStructureVisitor());
    }

    /**
     * listFiles
     *
     * @param  iterable  $files
     *
     * @return  void
     */
    public static function listFiles(iterable $files): void
    {
        foreach ($files as $file) {
            echo $file . "\n";
        }
    }

    /**
     * cleanPaths
     *
     * @param  iterable  $paths
     *
     * @return  mixed
     */
    public static function cleanPaths(iterable $paths): mixed
    {
        $p = [];

        foreach ($paths as $key => $path) {
            $p[$key] = Path::clean(FileObject::unwrap($path));
        }

        sort($p);

        return $p;
    }

    /**
     * getFilesRecursive
     *
     * @return  array
     */
    public static function getFilesRecursive(): array
    {
        return [
            static::$baseDir . '/file1.txt',
            static::$baseDir . '/folder1/level2/file3',
            static::$baseDir . '/folder1/path1',
            static::$baseDir . '/folder2/file2.html',
        ];
    }

    /**
     * getFoldersRecursive
     *
     * @return  array
     */
    public static function getFoldersRecursive(): array
    {
        return [
            static::$baseDir . '/folder1',
            static::$baseDir . '/folder1/level2',
            static::$baseDir . '/folder2',
        ];
    }

    /**
     * getItemsRecursive
     *
     * @return  array
     */
    public static function getItemsRecursive(): array
    {
        return [
            static::$baseDir . '/file1.txt',
            static::$baseDir . '/folder1',
            static::$baseDir . '/folder1/level2',
            static::$baseDir . '/folder1/level2/file3',
            static::$baseDir . '/folder1/path1',
            static::$baseDir . '/folder2',
            static::$baseDir . '/folder2/file2.html',
        ];
    }
}
