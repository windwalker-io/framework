<?php

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use DomainException;
use InvalidArgumentException;
use Windwalker\Filesystem\FileObject;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Utilities\Options\OptionAccessTrait;

use function Windwalker\fs;

/**
 * The FilesystemHandler class.
 */
class FilesystemHandler extends AbstractHandler
{
    use OptionAccessTrait;

    protected ?string $path;

    /**
     * FilesystemHandler constructor.
     *
     * @param  string|null  $path
     * @param  array{
     *     prefix?: string|null,
     *     gc_maxlifetime?: int|string|null
     * } $options
     */
    public function __construct(?string $path = null, array $options = [])
    {
        if (!class_exists(Filesystem::class)) {
            throw new DomainException('Please install windwalker/filesystem ^4.0');
        }

        $this->prepareOptions(
            [
                'prefix' => 'sess_',
            ],
            $options
        );

        $this->path = $path;
    }

    public function getFileName(string $id): string
    {
        if (preg_match('/\A[a-zA-Z0-9,-]+\z/', $id) !== 1) {
            throw new InvalidArgumentException('Session ID must contain only letters, digits, commas or hyphens.');
        }

        $file = $id;

        if ($this->getOption('prefix')) {
            $file = $this->getOption('prefix') . $file;
        }

        return $file;
    }

    public function getFilePath(string $id): string
    {
        $this->path ??= session_save_path();

        return $this->path . '/' . $this->getFileName($id);
    }

    /**
     * doRead
     *
     * @param  string  $id
     *
     * @return  string|null
     */
    protected function doRead(string $id): ?string
    {
        $file = Filesystem::get($this->getFilePath($id));

        if (!$file->exists()) {
            return null;
        }

        $lifetime = (int) ($this->getOption('gc_maxlifetime') ?? ini_get('session.gc_maxlifetime'));

        if ($file->getMTime() <= time() - $lifetime) {
            return null;
        }

        return (string) $file->read();
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return !class_exists(Filesystem::class);
    }

    /**
     * destroy
     *
     * @param  string  $id
     *
     * @return  bool
     */
    public function destroy($id): bool
    {
        fs($this->getFilePath($id))->deleteIfExists();

        return true;
    }

    /**
     * gc
     *
     * @param  int  $maxlifetime
     *
     * @return int|false  Returns the number of deleted sessions on success, or false on failure.
     */
    public function gc($maxlifetime): int|false
    {
        $past = time() - $maxlifetime;
        $prefix = (string) ($this->getOption('prefix') ?: '');
        $pattern = '/\A' . preg_quote($prefix, '/') . '[a-zA-Z0-9,-]+\z/';

        $files = Filesystem::files($this->path)
            ->filter(fn(FileObject $file) => preg_match($pattern, $file->getBasename()) === 1)
            ->filter(fn(FileObject $file) => $file->getMTime() < $past);

        $count = 0;

        /** @var FileObject $file */
        foreach ($files as $file) {
            $count++;
            $file->delete();
        }

        return $count;
    }

    /**
     * write
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function write($id, $data): bool
    {
        Filesystem::write($this->getFilePath($id), $data);

        return true;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        $file = Filesystem::get($this->getFilePath($id));

        if (!$file->exists()) {
            return false;
        }

        $file->touch(time());

        return true;
    }
}
