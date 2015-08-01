<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Request;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Uri\PsrUri;

/**
 * Test class of CurlTransport
 *
 * @since {DEPLOY_VERSION}
 */
class CurlTransportTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var CurlTransport
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
		$this->instance = new CurlTransport;
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

	public function testRequest()
	{
		$request = new Request;

		$request = $request->withUri(new PsrUri('http://example.com/foo?foo=bar'))
			->withMethod('GET');

		$response = $this->instance->request($request);

		show($response);
	}

	/**
	 * Method to test isSupported().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Transport\CurlTransport::isSupported
	 * @TODO   Implement testIsSupported().
	 */
	public function testIsSupported()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
