<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Test\Format;

use Windwalker\Language\Format\PhpFormat;

/**
 * Test class of PhpFormat
 *
 * @since {DEPLOY_VERSION}
 */
class PhpFormatTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var PhpFormat
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
		$this->instance = new PhpFormat;
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
	 * Method to test parse().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Format\PhpFormat::parse
	 */
	public function testParse()
	{
		$data = include __DIR__ . '/../fixtures/php/en-GB.php';

		$this->assertArrayHasKey('WINDWALKER_LANGUAGE_TEST_SAKURA', $this->instance->parse($data));
	}
}
