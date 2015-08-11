<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test;

use Windwalker\Http\Request;
use Windwalker\Uri\PsrUri;

/**
 * Test class of Request
 *
 * @since 2.1
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Request
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
		$this->instance = new Request;
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
	 * Method to test getHeaders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Request::getHeaders
	 */
	public function testGetHeaders()
	{
		$this->assertEquals(array(), $this->instance->getHeaders());

		$request = $this->instance->withUri(new PsrUri('http://windwalker.io/flower/sakura'));

		$this->assertEquals(array('Host' => array('windwalker.io')), $request->getHeaders());
	}

	/**
	 * Method to test getHeader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Request::getHeader
	 */
	public function testGetHeader()
	{
		$this->assertEquals(array(), $this->instance->getHeader('host'));

		$request = $this->instance->withUri(new PsrUri('http://windwalker.io/flower/sakura'));

		$this->assertEquals(array('windwalker.io'), $request->getHeader('host'));
	}
}
