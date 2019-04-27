<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Folder;

/**
 * The AbstractFilesystemTest class.
 *
 * @since  2.0
 */
abstract class AbstractFilesystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Property dest.
     *
     * @var string
     */
    protected static $dest;

    /**
     * Property src.
     *
     * @var string
     */
    protected static $src;

    /**
     * setUpBeforeClass
     *
     * @return  void
     */
    public static function setUpBeforeClass(): void
    {
        // @mkdir(__DIR__ . '/dest');
    }

    /**
     * tearDownAfterClass
     *
     * @return  void
     */
    public static function tearDownAfterClass(): void
    {
        if (is_dir(static::$dest)) {
            Folder::delete(static::$dest);
        }
    }

    /**
     * __desctuct
     */
    public function __destruct()
    {
        if (is_dir(static::$dest)) {
            Folder::delete(static::$dest);
        }
    }

    /**
     * setUp
     *
     * @return  void
     */
    protected function setUp(): void
    {
        static::$dest = __DIR__ . '/dest';
        static::$src = __DIR__ . '/files';

        Filesystem::copy(static::$src, static::$dest);
    }

    /**
     * tearDown
     *
     * @return  void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir(static::$dest)) {
            Folder::delete(static::$dest);
        }
    }
}
