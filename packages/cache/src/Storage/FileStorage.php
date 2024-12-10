<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException;
use Throwable;
use Windwalker\Utilities\Options\OptionAccessTrait;

use function Windwalker\str;

/**
 * The FilesystemStorage class.
 */
class FileStorage implements StorageInterface, LockableStorageInterface
{
    use OptionAccessTrait;
    use LockableStorageTrait;

    /**
     * @var string
     */
    protected string $root;

    /**
     * @var resource[]
     */
    protected array $lockedResources = [];

    /**
     * AbstractFormatterStorage constructor.
     *
     * @param  string  $root
     * @param  array   $options
     */
    public function __construct(string $root, array $options = [])
    {
        $this->root = $root;

        $this->prepareOptions(
            [
                'lock' => true,
                'deny_access' => false,
                'extension' => '.data',
                'expiration_format' => '/////---------- Expired At: %d ----------/////',
            ],
            $options
        );

        $this->checkFilePath($root);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $data = $this->read($key);

        if (!is_string($data)) {
            return $data;
        }

        return preg_replace(
            '#' . static::escapeRegex($this->getExpirationFormat()) . '#',
            '',
            (string) $data
        );
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        if (!$this->exists($key)) {
            return false;
        }

        if ($this->getExpirationFormat()) {
            $data = $this->read($key);

            preg_match(
                '#' . static::escapeRegex($this->getExpirationFormat()) . '#',
                $data,
                $matches
            );

            $expiration = $matches[1] ?? null;

            if (!static::isExpired((int) $expiration)) {
                return true;
            }

            $this->remove($key);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $filePath = $this->getRoot();
        $this->checkFilePath($filePath);

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($filePath)
            ),
            '/' . preg_quote($this->getOption('extension')) . '$/i'
        );

        $results = true;

        /* @var  RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $results = unlink($file->getRealPath()) && $results;
            }
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $filePath = $this->fetchStreamUri($key);

        if (!is_file($filePath)) {
            return true;
        }

        return @unlink($filePath);
    }

    /**
     * @inheritDoc
     */
    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        if ($this->getOption('deny_access', false)) {
            $value = $this->getOption('deny_code') . $value;
        }

        $expirationFormat = $this->getExpirationFormat();

        $value = sprintf($expirationFormat, $expiration) . $value;

        return $this->write($key, $value);
    }

    /**
     * Check that the file path is a directory and writable.
     *
     * @param  string  $filePath  A file path.
     *
     * @return  bool  The method will always return true, if it returns.
     *
     * @throws  RuntimeException if the file path is invalid.
     * @since   2.0
     */
    protected function checkFilePath(string $filePath): bool
    {
        if (!is_dir($filePath)) {
            try {
                if (!mkdir($filePath, 0755, true) && !is_dir($filePath)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $filePath));
                }
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException(sprintf('The base cache path `%s` is not writable.', $filePath));
        }

        return true;
    }

    /**
     * write
     *
     * @param  string  $key
     * @param  string  $value
     *
     * @return  bool
     */
    protected function write(string $key, string $value): bool
    {
        $filename = $this->fetchStreamUri($key);

        if ($this->isLocked($key)) {
            $fp = $this->lockedResources[$filename];

            return (bool) fwrite($fp, $value);
        }

        return (bool) file_put_contents(
            $filename,
            $value,
            $this->shouldLock() ? LOCK_EX : 0
        );
    }

    /**
     * read
     *
     * @param  string  $key
     *
     * @return  string
     */
    protected function read(string $key): mixed
    {
        if ($this->isLocked($key)) {
            $filename = $this->fetchStreamUri($key);

            $fp = $this->lockedResources[$filename];

            return stream_get_contents($fp);
        }

        $filename = $this->fetchStreamUri($key);

        $resource = @fopen($filename, 'rb');

        if (!$resource) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot open the resource.',
                    $filename
                )
            );
        }

        // If locking is enabled get a shared lock for reading on the resource.
        if ($this->shouldLock() && !flock($resource, LOCK_SH)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot obtain a lock.',
                    $filename
                )
            );
        }

        $data = stream_get_contents($resource);

        // If locking is enabled release the lock on the resource.
        if ($this->shouldLock() && !flock($resource, LOCK_UN)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot release the lock.',
                    $filename
                )
            );
        }

        fclose($resource);

        return $data;
    }

    /**
     * exists
     *
     * @param  string  $key
     *
     * @return  bool
     */
    protected function exists(string $key): bool
    {
        return is_file($this->fetchStreamUri($key));
    }

    /**
     * Get the full stream URI for the cache entry.
     *
     * @param  string  $key  The storage entry identifier.
     *
     * @return  string  The full stream URI for the cache entry.
     *
     * @throws  RuntimeException if the cache path is invalid.
     * @since   2.0
     */
    public function fetchStreamUri(string $key): string
    {
        $filePath = $this->getRoot();

        $this->checkFilePath($filePath);

        if ($this->getOption('deny_access', false)) {
            $this->options['extension'] = '.php';
        }

        return sprintf(
            '%s/%s' . $this->getOption('extension'),
            $filePath,
            self::hashFilename($key)
        );
    }

    /**
     * hashFilename
     *
     * @param  string  $key
     *
     * @return  string
     */
    public static function hashFilename(string $key): string
    {
        return '~' . hash('sha1', $key);
    }

    /**
     * getRoot
     *
     * @return  string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * isExpired
     *
     * @param  int       $expiration
     * @param  int|null  $time
     *
     * @return  bool
     */
    public static function isExpired(int $expiration, ?int $time = null): bool
    {
        $time ??= time();

        return $expiration !== 0 && $expiration <= $time;
    }

    public static function escapeRegex(string $regex): string
    {
        $regex = preg_quote($regex, '#');
        return str_replace('%d', '(\d+)', $regex);
    }

    /**
     * @return  mixed
     */
    protected function getExpirationFormat(): string
    {
        return (string) $this->getOption('expiration_format');
    }

    public function lock(string $key, ?bool &$isNew = null): bool
    {
        if (!$this->shouldLock()) {
            $isNew = false;

            return true;
        }

        if ($this->isLocked($key)) {
            $isNew = false;

            return true;
        }

        $filePath = $this->fetchStreamUri($key);

        $isNew = !file_exists($filePath);

        $this->lockedResources[$filePath] = fopen($filePath, 'cb+');

        return flock($this->lockedResources[$filePath], LOCK_EX);
    }

    public function release(string $key): bool
    {
        if (!$this->shouldLock()) {
            return true;
        }

        if (!$this->isLocked($key)) {
            return true;
        }

        $filePath = $this->fetchStreamUri($key);

        $fp = $this->lockedResources[$filePath];

        unset($this->lockedResources[$filePath]);

        $result = flock($fp, LOCK_UN);

        fclose($fp);

        return $result;
    }

    public function isLocked(string $key): bool
    {
        $filePath = $this->fetchStreamUri($key);

        return isset($this->lockedResources[$filePath]);
    }

    public function shouldLock(): bool
    {
        return (bool) $this->getOption('lock', false);
    }
}
