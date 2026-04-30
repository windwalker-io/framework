<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use League\Flysystem\Filesystem;
use RuntimeException;

/**
 * The FlysystemStorage class.
 */
class FlysystemStorage extends FileStorage
{
    /**
     * @var Filesystem
     */
    protected Filesystem $driver;

    /**
     * FlysystemStorage constructor.
     *
     * @param  Filesystem  $driver
     * @param  array       $options
     * @param  float       $pruneProbability
     */
    public function __construct(
        Filesystem $driver,
        array $options = [],
        float $pruneProbability = 0.01,
        string $group = '',
    ) {
        $this->driver = $driver;

        parent::__construct('.', $options, $pruneProbability, $group);
    }

    /**
     * @inheritDoc
     */
    protected function read(string $key): string
    {
        return (string) $this->getDriver()->read($this->fetchStreamUri($key));
    }

    /**
     * @inheritDoc
     */
    protected function write(string $key, string $value): bool
    {
        $this->getDriver()->write($this->fetchStreamUri($key), $value);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function exists(string $key): bool
    {
        return $this->getDriver()->fileExists($this->fetchStreamUri($key));
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $results = true;
        $prefix = $this->getGroupPrefixPath();

        foreach ($this->getDriver()->listContents('/', true) as $metadata) {
            if ($this->getMetadataType($metadata) !== 'file') {
                continue;
            }

            $path = $this->getMetadataPath($metadata);

            if ($path === null) {
                continue;
            }

            if ($prefix !== '' && !str_starts_with($path, $prefix . '/')) {
                continue;
            }

            $results = $this->getDriver()->delete($path) && $results;
        }

        return $results;
    }

    public function prune(): int
    {
        if ($this->getExpirationFormat() === '') {
            return 0;
        }

        $pruned = 0;
        $prefix = $this->getGroupPrefixPath();

        foreach ($this->getDriver()->listContents('/', true) as $metadata) {
            if ($this->getMetadataType($metadata) !== 'file') {
                continue;
            }

            $path = $this->getMetadataPath($metadata);

            if ($path === null) {
                continue;
            }

            if ($prefix !== '' && !str_starts_with($path, $prefix . '/')) {
                continue;
            }

            $contents = $this->getDriver()->read($path);

            if (!is_string($contents)) {
                continue;
            }

            $expiration = $this->extractExpirationFromString($contents);

            if ($expiration !== null && static::isExpired($expiration)) {
                $this->getDriver()->delete($path);
                $pruned++;
            }
        }

        return $pruned;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $this->getDriver()->delete($this->fetchStreamUri($key));

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function checkFilePath($filePath): bool
    {
        return true;
    }

    /**
     * Method to get property Driver
     *
     * @return  Filesystem
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDriver(): Filesystem
    {
        return $this->driver;
    }

    /**
     * Method to set property driver
     *
     * @param  Filesystem  $driver
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setDriver(Filesystem $driver): static
    {
        $this->driver = $driver;

        return $this;
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
        $filePath = $this->getGroupPrefixPath();

        $this->checkFilePath($filePath);

        $ext = '.data';

        if ($this->getOption('deny_access', false)) {
            $ext = '.php';
        }

        if ($filePath === '') {
            return self::hashFilename($key) . $ext;
        }

        return $filePath . '/' . self::hashFilename($key) . $ext;
    }

    protected function getGroupPrefixPath(): string
    {
        return $this->group === '' ? '' : $this->getGroupDirectoryName();
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

    private function getMetadataPath(mixed $metadata): ?string
    {
        return match (true) {
            is_array($metadata) => $metadata['path'] ?? null,
            is_object($metadata) && method_exists($metadata, 'path') => $metadata->path(),
            default => null,
        };
    }

    private function getMetadataType(mixed $metadata): ?string
    {
        return match (true) {
            is_array($metadata) => $metadata['type'] ?? 'file',
            is_object($metadata) && method_exists($metadata, 'type') => $metadata->type(),
            default => null,
        };
    }
}
