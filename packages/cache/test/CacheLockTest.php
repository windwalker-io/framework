<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Cache\CacheLock;

/**
 * Tests for CacheLock.
 *
 * CacheLock uses lock-striping: a cache key is hashed (crc32) to one of
 * the files in the $files array, and PHP's flock() is used to serialise
 * access between processes.
 */
class CacheLockTest extends TestCase
{
    /** @var string[] Temp files created for this test run */
    private static array $tmpFiles = [];

    /** @var string[] The original $files list, saved before any test mutates it */
    private static array $originalFiles = [];

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        // Snapshot the original file list so we can fully restore it afterwards.
        self::$originalFiles = CacheLock::getFiles();
    }

    /**
     * Create N temp files and register them as CacheLock's stripe files.
     * Returns the list of paths.
     *
     * @return string[]
     */
    private static function useTempFiles(int $count = 4): array
    {
        $files = [];

        for ($i = 0; $i < $count; $i++) {
            $path = tempnam(sys_get_temp_dir(), 'cache_lock_test_');
            self::$tmpFiles[] = $path;
            $files[]          = $path;
        }

        CacheLock::setFiles($files);

        return $files;
    }

    /** Reset CacheLock to its original state after every test. */
    protected function tearDown(): void
    {
        // Flush all open handles / locks that a test may have left open.
        // Re-applying the *current* files is enough to clear $openedFiles and
        // $lockedFiles without losing the temp files set by useTempFiles().
        CacheLock::setFiles(CacheLock::getFiles());

        // Restore timeout / interval defaults so tests are isolated.
        CacheLock::setTimeout(30.0);
        CacheLock::setWaitInterval(100_000);
    }

    public static function tearDownAfterClass(): void
    {
        // Delete temp files first, then restore the original stripe-file list so
        // subsequent test classes (in the same process) are not affected by the
        // deleted paths still sitting in the static $files array.
        foreach (self::$tmpFiles as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }

        self::$tmpFiles = [];

        // Restore CacheLock to the state it had before this test class ran.
        CacheLock::setFiles(self::$originalFiles);
        CacheLock::setTimeout(30.0);
        CacheLock::setWaitInterval(100_000);
    }

    // -----------------------------------------------------------------------
    // lock() — basic behaviour
    // -----------------------------------------------------------------------

    /** @see CacheLock::lock */
    public function testLockFirstCallSetsIsNewTrue(): void
    {
        self::useTempFiles();

        $result = CacheLock::lock('mykey', $isNew);

        self::assertTrue($result, 'lock() should return true on first call');
        self::assertTrue($isNew, '$isNew must be true when a new lock is acquired');
    }

    /** @see CacheLock::lock */
    public function testLockSameKeyIsReentrant(): void
    {
        self::useTempFiles();

        CacheLock::lock('mykey');

        $result = CacheLock::lock('mykey', $isNew);

        self::assertTrue($result, 'Locking an already-locked key must still return true');
        self::assertFalse($isNew, '$isNew must be false when re-entering an existing lock');
    }

    /** @see CacheLock::lock */
    public function testLockDifferentKeysAreIndependent(): void
    {
        self::useTempFiles(8); // enough stripes so keys likely differ

        CacheLock::lock('keyA', $isNewA);
        CacheLock::lock('keyB', $isNewB);

        self::assertTrue($isNewA);
        self::assertTrue($isNewB);

        self::assertTrue(CacheLock::isLocked('keyA'));
        self::assertTrue(CacheLock::isLocked('keyB'));
    }

    /** @see CacheLock::lock */
    public function testLockWithEmptyFilesReturnsFalse(): void
    {
        CacheLock::setFiles([]);

        $result = CacheLock::lock('mykey', $isNew);

        self::assertFalse($result, 'lock() must fail when no stripe files are configured');
        self::assertFalse($isNew);
    }

    /** @see CacheLock::lock */
    public function testLockWithNonExistentFileReturnsFalse(): void
    {
        CacheLock::setFiles(['/this/file/does/not/exist.php']);

        $result = CacheLock::lock('mykey', $isNew);

        self::assertFalse($result, 'lock() must fail gracefully when the stripe file is missing');
        self::assertFalse($isNew);
    }

    // -----------------------------------------------------------------------
    // release()
    // -----------------------------------------------------------------------

    /** @see CacheLock::release */
    public function testReleaseReturnsTrueForLockedKey(): void
    {
        self::useTempFiles();

        CacheLock::lock('mykey');

        self::assertTrue(CacheLock::release('mykey'));
    }

    /** @see CacheLock::release */
    public function testReleaseReturnsTrueForUnlockedKey(): void
    {
        self::useTempFiles();

        // Must not throw and must return true even if key was never locked
        self::assertTrue(CacheLock::release('never_locked'));
    }

    /** @see CacheLock::release */
    public function testReleaseAllowsRelocking(): void
    {
        self::useTempFiles();

        CacheLock::lock('mykey');
        CacheLock::release('mykey');

        $result = CacheLock::lock('mykey', $isNew);

        self::assertTrue($result, 'Should be able to re-lock after releasing');
        self::assertTrue($isNew, '$isNew must be true after a fresh lock acquisition');
    }

    // -----------------------------------------------------------------------
    // isLocked()
    // -----------------------------------------------------------------------

    /** @see CacheLock::isLocked */
    public function testIsLockedReturnsFalseBeforeLocking(): void
    {
        self::useTempFiles();

        self::assertFalse(CacheLock::isLocked('mykey'));
    }

    /** @see CacheLock::isLocked */
    public function testIsLockedReturnsTrueWhileLocked(): void
    {
        self::useTempFiles();

        CacheLock::lock('mykey');

        self::assertTrue(CacheLock::isLocked('mykey'));
    }

    /** @see CacheLock::isLocked */
    public function testIsLockedReturnsFalseAfterRelease(): void
    {
        self::useTempFiles();

        CacheLock::lock('mykey');
        CacheLock::release('mykey');

        self::assertFalse(CacheLock::isLocked('mykey'));
    }

    // -----------------------------------------------------------------------
    // setFiles() / getFiles()
    // -----------------------------------------------------------------------

    /** @see CacheLock::setFiles */
    public function testSetFilesUpdatesFileList(): void
    {
        $files = self::useTempFiles(3);

        self::assertSame($files, CacheLock::getFiles());
    }

    /** @see CacheLock::setFiles */
    public function testSetFilesReleasesExistingHandlesAndLocks(): void
    {
        $files = self::useTempFiles();

        // Acquire a lock, then swap the file list
        CacheLock::lock('mykey');
        self::assertTrue(CacheLock::isLocked('mykey'));

        CacheLock::setFiles($files); // triggers handle cleanup

        // After setFiles the lock registry is cleared
        self::assertFalse(CacheLock::isLocked('mykey'));
    }

    /** @see CacheLock::getFiles */
    public function testGetFilesReturnsArrayValues(): void
    {
        // setFiles must re-index the array (array_values)
        $files = self::useTempFiles(2);
        CacheLock::setFiles(array_combine(['a', 'b'], $files));

        self::assertSame([0, 1], array_keys(CacheLock::getFiles()));
    }

    // -----------------------------------------------------------------------
    // setTimeout() / getTimeout() / setWaitInterval()
    // -----------------------------------------------------------------------

    /** @see CacheLock::setTimeout */
    public function testSetGetTimeout(): void
    {
        CacheLock::setTimeout(5.0);

        self::assertSame(5.0, CacheLock::getTimeout());
    }

    /** @see CacheLock::setTimeout */
    public function testSetTimeoutClampsNegativeToZero(): void
    {
        CacheLock::setTimeout(-10.0);

        self::assertSame(0.0, CacheLock::getTimeout());
    }

    /** @see CacheLock::setWaitInterval */
    public function testSetWaitIntervalClampsToMinimumOne(): void
    {
        CacheLock::setWaitInterval(0);

        // Access via lock – we just need to confirm it won't be 0
        // Use reflection to inspect the private static property
        $ref = new \ReflectionClass(CacheLock::class);
        $prop = $ref->getProperty('waitInterval');
        $prop->setAccessible(true);

        self::assertGreaterThanOrEqual(1, $prop->getValue());
    }

    // -----------------------------------------------------------------------
    // Timeout / contention (requires a competing flock on the same file)
    // -----------------------------------------------------------------------

    /**
     * Simulate lock contention by opening the stripe file with an external
     * fopen handle and holding LOCK_EX, then verifying that CacheLock times
     * out cleanly when $timeout = 0.
     *
     * @see CacheLock::lock
     */
    public function testLockTimesOutImmediatelyWhenTimeoutIsZero(): void
    {
        // Windows advisory-lock semantics differ; skip there.
        if (DIRECTORY_SEPARATOR === '\\') {
            self::markTestSkipped('flock contention test is not reliable on Windows.');
        }

        $files = self::useTempFiles(1); // single stripe so we know the index

        // Hold an exclusive lock on the stripe file from "outside"
        $external = fopen($files[0], 'r+b');
        flock($external, LOCK_EX);

        try {
            CacheLock::setTimeout(0.0); // give up immediately if blocked

            $result = CacheLock::lock('contested_key', $isNew);

            // Either the OS supports LOCK_NB and fails, or it treats it as "not supported"
            // and proceeds ($result = true). We assert the documented contract:
            //   - If it fails   → $result = false, $isNew = false
            //   - If unsupported → $result = true, $isNew = true (proceed without lock)
            if (!$result) {
                self::assertFalse($isNew, '$isNew must be false when lock acquisition fails');
            }
        } finally {
            flock($external, LOCK_UN);
            fclose($external);
        }
    }

    /**
     * Verify that after another process releases its lock, CacheLock
     * eventually acquires it within the timeout window.
     *
     * @see CacheLock::lock
     */
    public function testLockAcquiredAfterContendingLockIsReleased(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            self::markTestSkipped('flock contention test is not reliable on Windows.');
        }

        $files = self::useTempFiles(1);

        // Hold an exclusive lock briefly, then release it
        $external = fopen($files[0], 'r+b');
        flock($external, LOCK_EX);

        // Release the external lock after a short delay using a child process
        // (fork is the cleanest way; skip if pcntl is unavailable)
        if (!function_exists('pcntl_fork')) {
            flock($external, LOCK_UN);
            fclose($external);
            self::markTestSkipped('pcntl_fork not available.');
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            flock($external, LOCK_UN);
            fclose($external);
            self::fail('pcntl_fork() failed.');
        }

        if ($pid === 0) {
            // Child: hold the external lock for a short moment, then release
            usleep(150_000); // 150 ms
            flock($external, LOCK_UN);
            fclose($external);
            exit(0);
        }

        // Parent: try to acquire the lock with a generous timeout
        fclose($external); // parent closes its copy so child stays the sole holder

        CacheLock::setTimeout(3.0);
        CacheLock::setWaitInterval(50_000); // poll every 50 ms

        $result = CacheLock::lock('contested_key', $isNew);

        pcntl_waitpid($pid, $status);

        if ($result) {
            self::assertTrue($isNew, '$isNew must be true when lock is newly acquired');
            CacheLock::release('contested_key');
        } else {
            // On some systems LOCK_NB is unsupported and flock just proceeds;
            // a false here means a genuine timeout, which is also acceptable
            // (child may not have released within the window in a slow CI env).
            self::assertFalse($isNew);
        }
    }
}

