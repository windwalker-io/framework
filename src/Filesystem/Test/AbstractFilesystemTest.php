<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Folder;

/**
 * The AbstractFilesystemTest class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractFilesystemTest extends \PHPUnit_Framework_TestCase
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
	public static function setUpBeforeClass()
	{
		// @mkdir(__DIR__ . '/dest');
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		if (is_dir(static::$dest))
		{
			Folder::delete(static::$dest);
		}
	}

	/**
	 * __desctuct
	 */
	public function __destruct()
	{
		if (is_dir(static::$dest))
		{
			Folder::delete(static::$dest);
		}
	}

	/**
	 * setUp
	 *
	 * @return  void
	 */
	protected function setUp()
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
	protected function tearDown()
	{
		parent::tearDown();

		if (is_dir(static::$dest))
		{
			Folder::delete(static::$dest);
		}
	}
}
