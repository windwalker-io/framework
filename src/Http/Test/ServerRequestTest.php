<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test;

use Windwalker\Http\ServerRequest;
use Windwalker\Http\UploadedFile;
use Windwalker\Test\TestHelper;
use Windwalker\Uri\PsrUri;

/**
 * Test class of ServerRequest
 *
 * @since {DEPLOY_VERSION}
 */
class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var ServerRequest
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
		$this->instance = new ServerRequest;
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
	 * testConstruct
	 *
	 * @return  void
	 */
	public function testConstruct()
	{
		$server = array(
			'foo' => 'bar',
			'baz' => 'bat',
		);

		$server['server'] = true;

		$files = array(
			'files' => new UploadedFile('php://temp', 0),
		);

		$uri = new PsrUri('http://example.com');
		$method = 'POST';
		$headers = array(
			'Host' => array('example.com'),
		);

		$request = new ServerRequest(
			$server,
			$files,
			$uri,
			$method,
			'php://memory',
			$headers
		);

		$this->assertEquals($server, $request->getServerParams());
		$this->assertEquals($files, $request->getUploadedFiles());

		$this->assertSame($uri, $request->getUri());
		$this->assertEquals($method, $request->getMethod());
		$this->assertEquals($headers, $request->getHeaders());

		$body = $request->getBody();
		$stream = TestHelper::getValue($body, 'stream');

		$this->assertEquals('php://memory', $stream);
	}

	/**
	 * Method to test getServerParams().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getServerParams
	 * @TODO   Implement testGetServerParams().
	 */
	public function testGetServerParams()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCookieParams().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getCookieParams
	 * @TODO   Implement testGetCookieParams().
	 */
	public function testGetCookieParams()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withCookieParams().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withCookieParams
	 * @TODO   Implement testWithCookieParams().
	 */
	public function testWithCookieParams()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getQueryParams().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getQueryParams
	 * @TODO   Implement testGetQueryParams().
	 */
	public function testGetQueryParams()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withQueryParams().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withQueryParams
	 * @TODO   Implement testWithQueryParams().
	 */
	public function testWithQueryParams()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getUploadedFiles().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getUploadedFiles
	 * @TODO   Implement testGetUploadedFiles().
	 */
	public function testGetUploadedFiles()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withUploadedFiles().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withUploadedFiles
	 * @TODO   Implement testWithUploadedFiles().
	 */
	public function testWithUploadedFiles()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getParsedBody().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getParsedBody
	 * @TODO   Implement testGetParsedBody().
	 */
	public function testGetParsedBody()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withParsedBody().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withParsedBody
	 * @TODO   Implement testWithParsedBody().
	 */
	public function testWithParsedBody()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getAttributes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getAttributes
	 * @TODO   Implement testGetAttributes().
	 */
	public function testGetAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::getAttribute
	 * @TODO   Implement testGetAttribute().
	 */
	public function testGetAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withAttribute
	 * @TODO   Implement testWithAttribute().
	 */
	public function testWithAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test withoutAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\ServerRequest::withoutAttribute
	 * @TODO   Implement testWithoutAttribute().
	 */
	public function testWithoutAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
