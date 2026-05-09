<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use Psr\Log\LoggerInterface;

class CacheLock
{
    private static array $openedFiles = [];

    private static array $lockedFiles = [];

    /**
     * Timeout in seconds to wait for an exclusive lock before giving up.
     * Set to 0 to use a simple blocking lock with no timeout.
     */
    private static float $timeout = 30.0;

    /**
     * Polling interval in microseconds when waiting for a lock.
     */
    private static int $waitInterval = 100_000;

    public static ?LoggerInterface $logger = null;

    private static array $files = [
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'ArrayStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'DatabaseStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'FileStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'FlysystemStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'ForeverFileStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'MemcachedStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'ModifiedFileStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'NullStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'PhpFileStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'RedisStorage.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'StorageInterface.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'JsonAssocSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'JsonSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'PhpFileSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'PhpSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'RawSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'SerializerInterface.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Serializer' . DIRECTORY_SEPARATOR . 'StringSerializer.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR . 'RuntimeException.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR . 'InvalidArgumentException.php',
    ];


    public static function lock(string $key, ?bool &$isNew = null): bool
    {
        $logger = static::$logger;

        // Already locked by this process — return without acquiring again
        if (isset(static::$lockedFiles[$key])) {
            $isNew = false;
            $logger?->debug('Cache lock: Lock already held by this process', ['key' => $key]);

            return true;
        }

        $files = static::$files;

        if (empty($files)) {
            $isNew = false;
            $logger?->warning('Cache lock: No lock files configured', ['key' => $key]);

            return false;
        }

        // Map the key to one of the lock files using lock striping
        $index = abs(crc32($key)) % count($files);

        $fp = static::open($index);

        if ($fp === false) {
            $isNew = false;
            $logger?->error('Cache lock: Failed to open lock file', ['key' => $key, 'index' => $index]);

            return false;
        }

        // Try a non-blocking exclusive lock first.
        // $locked = true            → lock acquired, proceed
        // $locked = false, $wouldBlock = 0 → locking not supported by filesystem, proceed anyway
        // $locked = false, $wouldBlock = 1 → another process holds the lock, wait with timeout
        $locked = flock($fp, LOCK_EX | LOCK_NB, $wouldBlock);

        if (!$locked && $wouldBlock) {
            if (static::$timeout <= 0) {
                // No timeout configured — give up immediately
                $isNew = false;
                $logger?->info('Cache lock: Lock timeout disabled, giving up immediately', ['key' => $key]);

                return false;
            }

            // Another process holds the lock — poll until timeout
            $logger?->debug('Cache lock: Waiting for lock to be released', [
                'key' => $key,
                'timeout' => static::$timeout,
            ]);
            $deadline = microtime(true) + static::$timeout;
            $acquired = false;

            do {
                usleep(static::$waitInterval);
                $acquired = flock($fp, LOCK_EX | LOCK_NB, $wouldBlock);
            } while (!$acquired && $wouldBlock && microtime(true) < $deadline);

            if (!$acquired && $wouldBlock) {
                // Timeout expired and lock is still held by another process
                $isNew = false;
                $logger?->warning('Cache lock: Failed to acquire lock within timeout', [
                    'key' => $key,
                    'timeout' => static::$timeout,
                ]);

                return false;
            }

            // $acquired = true: lock obtained after waiting
            // $acquired = false && !$wouldBlock: locking became unsupported mid-wait, proceed
            $locked = $acquired || !$wouldBlock;
        }

        // At this point: $locked is true (acquired) or the filesystem does not support
        // advisory locking at all (!$wouldBlock on the initial attempt) — proceed either way.
        if (!$locked) {
            $isNew = false;

            return false;
        }

        static::$openedFiles[$index] = $fp;
        static::$lockedFiles[$key] = $index;

        $isNew = true;
        $logger?->debug('Cache lock: Lock acquired successfully', ['key' => $key]);

        return true;
    }

    public static function release(string $key): bool
    {
        $logger = static::$logger;

        if (!isset(static::$lockedFiles[$key])) {
            $logger?->debug('Cache lock: Key not locked, nothing to release', ['key' => $key]);

            return true;
        }

        $index = static::$lockedFiles[$key];
        $fp = static::$openedFiles[$index];

        unset(static::$lockedFiles[$key]);

        // Keep the file handle open for reuse (like Symfony's approach)
        $released = flock($fp, LOCK_UN);

        if ($released) {
            $logger?->debug('Cache lock: Lock released successfully', ['key' => $key]);
        } else {
            $logger?->warning('Cache lock: Failed to release lock', ['key' => $key]);
        }

        return $released;
    }

    public static function isLocked(string $key): bool
    {
        return isset(static::$lockedFiles[$key]);
    }

    public static function setFiles(array $files): void
    {
        // Close all open handles before switching file set
        foreach (static::$openedFiles as $fp) {
            if ($fp) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }

        static::$files = array_values($files);
        static::$openedFiles = [];
        static::$lockedFiles = [];
    }

    public static function getFiles(): array
    {
        return self::$files;
    }

    public static function setTimeout(float $seconds): void
    {
        static::$timeout = max(0.0, $seconds);
    }

    public static function getTimeout(): float
    {
        return static::$timeout;
    }

    public static function setWaitInterval(int $microseconds): void
    {
        static::$waitInterval = max(1, $microseconds);
    }

    /**
     * Open (or reuse) the file handle for the given stripe index.
     *
     * @return resource|false
     */
    private static function open(int $index)
    {
        if (isset(static::$openedFiles[$index]) && static::$openedFiles[$index] !== false) {
            return static::$openedFiles[$index];
        }

        $file = static::$files[$index] ?? null;

        if ($file === null || !is_file($file)) {
            return static::$openedFiles[$index] = false;
        }

        set_error_handler(static fn() => null);
        try {
            // Try read-write first so LOCK_EX works on all platforms;
            // fall back to read-only (advisory LOCK_EX still works on POSIX).
            $h = fopen($file, 'r+b') ?: fopen($file, 'rb');
        } finally {
            restore_error_handler();
        }

        return static::$openedFiles[$index] = ($h ?: false);
    }
}
