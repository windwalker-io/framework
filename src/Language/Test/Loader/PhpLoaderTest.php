<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language\Test\Loader;

use Windwalker\Language\Loader\PhpLoader;

/**
 * Test class of PhpLoader
 *
 * @since 2.0
 */
class PhpLoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var PhpLoader
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new PhpLoader;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test load().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Loader\PhpLoader::load
	 */
	public function testLoad()
	{
		$data = $this->instance->load(__DIR__ . '/../fixtures/php/en-GB.php');

		$this->assertArrayHasKey('WINDWALKER_LANGUAGE_TEST_FLOWER', $data);
	}
}
