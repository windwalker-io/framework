<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem;

use AppendIterator;
use BadMethodCallException;
use DomainException;
use FilesystemIterator;
use Psr\Http\Message\StreamInterface;
use Traversable;
use Webmozart\Glob\Glob;
use Webmozart\Glob\Iterator\GlobIterator;
use Windwalker\Data\Collection;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\Iterator\FilesIterator;
use Windwalker\Promise\Promise;
use Windwalker\Scalars\StringObject;
use Windwalker\Stream\Stream;
use Windwalker\Utilities\Iterator\UniqueIterator;
use Windwalker\Utilities\Str;

use function Windwalker\uid;

/**
 * Class Filesystem
 *
 * @see   FileObject
 *
 * @method static FileObject      mkdir(string $path = '', int $mode = 0755)
 * @method static FileObject      copy(string $src, string $dest, bool $force = false)
 * @method static FileObject      move(string $src, string $dest, bool $force = false)
 * @method static StringObject    read(string $path)
 * @method static StreamInterface readStream(string $path, string $mode = Stream::MODE_READ_ONLY_FROM_BEGIN)
 * @method static Collection      readAndParse(?string $format = null, array $options = [])
 * @method static FileObject      write(string $path, string $buffer)
 * @method static FileObject      writeStream(string $path, string|resource|StreamInterface $stream)
 * @method static FileObject      delete(string $path)
 * @method static FileObject      deleteIfExists(string $path)
 * @method static FilesIterator   files(string $path, bool $recursive = false, ?int $flags = null)
 * @method static FilesIterator   folders(string $path, bool $recursive = false, ?int $flags = null)
 * @method static FilesIterator   items(string $path, bool $recursive = false, ?int $flags = null)
 * @method static Promise mkdirAsync(string $path = '', int $mode = 0755)
 * @method static Promise copyAsync(string $src, string $dest, bool $force = false)
 * @method static Promise moveAsync(string $src, string $dest, bool $force = false)
 * @method static Promise readAsync(string $path)
 * @method static Promise readStreamAsync(string $path, string $mode = Stream::MODE_READ_ONLY_FROM_BEGIN)
 * @method static Promise writeAsync(string $path, string $buffer)
 * @method static Promise writeStreamAsync(string $path, string|resource|StreamInterface $stream)
 * @method static Promise deleteAsync(string $path)
 * @method static Promise deleteIfExistsAsync(string $path)
 * @method static Promise filesAsync(string $path, bool $recursive = false, ?int $flags = null)
 * @method static Promise foldersAsync(string $path, bool $recursive = false, ?int $flags = null)
 * @method static Promise itemsAsync(string $path, bool $recursive = false, ?int $flags = null)
 * @method static Promise createTempAsync(?string $dir = null, ?string $prefix = null)
 *
 * @since 2.0
 */
class Filesystem
{
    public const DELETE_WHEN_SHUTDOWN = 1 << 0;

    public const DELETE_WHEN_DESTRUCT = 1 << 1;

    /**
     * Get a path as FileObject.
     *
     * @param  string  $path
     * @param  string|null  $root
     *
     * @return  FileObject
     */
    public static function get(string $path, ?string $root = null): FileObject
    {
        return new FileObject($path, $root);
    }

    /**
     * Glob with Ant-like pattern. This method based on webmozart/glob.
     *
     * @param  string  $path
     * @param  int     $flags
     *
     * @return  FilesIterator
     */
    public static function glob(
        string $path,
        int $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
    ): FilesIterator {
        if (!class_exists(GlobIterator::class)) {
            throw new DomainException('Please install webmozart/glob first');
        }

        // Webmozart/glob must use `/` in windows.
        $path = Path::clean($path, '/');

        return new FilesIterator(new GlobIterator($path, $flags), Glob::getBasePath($path));
    }

    /**
     * Glob multiple paths with unique return paths.
     *
     * @param  array  $paths
     * @param  int    $flags
     *
     * @return  FilesIterator
     */
    public static function globAll(
        string|array $paths,
        int $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
    ): FilesIterator {
        if (!class_exists(GlobIterator::class)) {
            throw new DomainException('Please install webmozart/glob first');
        }

        $paths = (array) $paths;

        $excludes = [];
        $excludePaths = [];
        $includePaths = [];

        foreach ($paths as $path) {
            if (str_starts_with($path, '!')) {
                $excludePaths[] = Str::removeLeft($path, '!');
            } else {
                $includePaths[] = $path;
            }
        }

        if ($excludePaths !== []) {
            $excludes = static::globAll($excludePaths)->toArray();

            $excludes = array_map(fn($path) => Path::normalize($path), $excludes);
        }

        $iter = new AppendIterator();

        foreach ($includePaths as $path) {
            // Webmozart/glob must use `/` in windows.
            $path = Path::clean($path, '/');
            $iter->append(new FilesIterator(new GlobIterator($path, $flags), Glob::getBasePath($path)));
        }

        $iter = (new FilesIterator(new UniqueIterator($iter)));

        if ($excludes !== []) {
            $iter = $iter->filter(
                fn(FileObject $file) => !in_array(
                    Path::normalize($file->getPathname()),
                    $excludes,
                    true
                )
            );
        }

        return $iter;
    }

    /**
     * iteratorToArray
     *
     * @param  Traversable  $iterator
     *
     * @return  array
     */
    public static function toArray(Traversable $iterator): array
    {
        $array = [];

        foreach ($iterator as $key => $file) {
            $array[] = FileObject::unwrap($file);
        }

        return $array;
    }

    /**
     * createTemp
     *
     * @param  string|null  $dir
     * @param  string|null  $prefix
     * @param  int          $flags
     *
     * @return TempFileObject
     *
     * @since  3.5.12
     */
    public static function createTemp(
        ?string $dir = null,
        ?string $prefix = null,
        int $flags = self::DELETE_WHEN_DESTRUCT
    ): TempFileObject {
        $dir = $dir ?? sys_get_temp_dir();
        $prefix = $prefix ?? 'Windwalker-Temp-';

        if (!is_dir($dir)) {
            static::mkdir($dir);
        }

        $temp = tempnam($dir, $prefix);

        if (!$temp) {
            throw new FilesystemException(
                sprintf(
                    'Create temp file on %s failure.',
                    $dir
                )
            );
        }

        $file = new TempFileObject($temp);

        if ($flags & static::DELETE_WHEN_DESTRUCT) {
            $file->deleteWhenDestruct(true);
        }

        if ($flags & static::DELETE_WHEN_SHUTDOWN) {
            $file->deleteWhenShutdown();
        }

        return $file;
    }

    public static function createTempFolder(
        ?string $dir = null,
        ?string $prefix = null,
        int $flags = self::DELETE_WHEN_DESTRUCT
    ): TempFileObject {
        $dir = $dir ?? sys_get_temp_dir();
        $prefix = $prefix ?? 'Windwalker-Temp-Folder-';

        $temp = $prefix . uid();
        $tempPath = $dir . DIRECTORY_SEPARATOR . $temp;

        static::mkdir($dir . DIRECTORY_SEPARATOR . $temp);

        $folder = new TempFileObject($tempPath);

        if ($flags & static::DELETE_WHEN_DESTRUCT) {
            $folder->deleteWhenDestruct(true);
        }

        if ($flags & static::DELETE_WHEN_SHUTDOWN) {
            $folder->deleteWhenShutdown();
        }

        return $folder;
    }

    /**
     * Make a symlink. In Windows, if is directory, will try to make it with Junction, that can not required
     * the admin permissions.
     *
     * @param  string  $target
     * @param  string  $link
     *
     * @return  bool
     */
    public static function symlink(string $target, string $link): bool
    {
        $windows = defined('PHP_WINDOWS_VERSION_BUILD');

        $target = Path::normalize($target);
        $link = Path::normalize($link);

        if ($windows) {
            if (is_file($target)) {
                // Files can only use symbolic link.
                exec("mklink /D {$link} {$target}", $output, $returnVar);
            } else {
                // Try make dir link by junction.
                exec("mklink /j {$link} {$target}", $output, $returnVar);
            }

            return $returnVar === 0;
        }

        return symlink($target, $link);
    }

    /**
     * __callStatic
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  void
     */
    public static function __callStatic(string $name, array $args)
    {
        $maps = [
            'read',
            'readStream',
            'write',
            'writeStream',
            'mkdir',
            'copy' => 'copyTo',
            'move' => 'moveTo',
            'delete',
            'deleteIfExists',
            'files',
            'folders',
            'items',
            'readAsync',
            'readStreamAsync',
            'writeAsync',
            'writeStreamAsync',
            'mkdirAsync',
            'copyAsync' => 'copyToAsync',
            'moveAsync' => 'moveToAsync',
            'deleteAsync',
            'deleteIfExistsAsync',
            'filesAsync',
            'foldersAsync',
            'itemsAsync',
            'createTempAsync',
        ];

        if (isset($maps[$name])) {
            $method = $maps[$name];
        } elseif (in_array($name, $maps, true)) {
            $method = $name;
        }

        if (isset($method)) {
            $path = array_shift($args);

            return static::get($path)->$method(...$args);
        }

        throw new BadMethodCallException(sprintf('Method %s::%s not exists.', static::class, $name));
    }
}
