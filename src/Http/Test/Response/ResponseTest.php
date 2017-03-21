<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Response;

use Windwalker\Http\Response\Response;
use Windwalker\Http\Stream\Stream;

/**
 * Test class of Response
 *
 * @since 2.1
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Response
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
		$this->instance = new Response;
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

	public function testConstruct()
	{
		// Test no params
		$res = new Response;

		$this->assertInstanceOf('Windwalker\Http\Stream\Stream', $res->getBody());
		$this->assertEquals('php://memory', $res->getBody()->getMetadata('uri'));
		$this->assertEquals(200, $res->getStatusCode());
		$this->assertEquals(array(), $res->getHeaders());

		// Test with params
		$body = fopen($tmpfile = tempnam(sys_get_temp_dir(), 'windwalker'), 'wb+');
		$headers = array(
			'X-Foo' => array('Flower', 'Sakura'),
			'Content-Type' => 'application/json'
		);

		$res = new Response($body, 404, $headers);

		$this->assertInstanceOf('Windwalker\Http\Stream\Stream', $res->getBody());
		$this->assertEquals($tmpfile, $res->getBody()->getMetadata('uri'));
		$this->assertEquals(array('Flower', 'Sakura'), $res->getHeader('x-foo'));
		$this->assertEquals(array('application/json'), $res->getHeader('content-type'));

		fclose($body);

		// Test with object params
		$body = new Stream;
		$res = new Response($body);

		$this->assertSame($body, $res->getBody());
	}

	/**
	 * Method to test getStatusCode().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Response\Response::getStatusCode()
	 * @covers \Windwalker\Http\Response\Response::withStatus
	 */
	public function testWithAndGetStatusCode()
	{
		$this->assertEquals(200, $this->instance->getStatusCode());

		$res = $this->instance->withStatus(403);

		$this->assertNotSame($res, $this->instance);
		$this->assertEquals(403, $res->getStatusCode());

		$res = $res->withStatus(500, 'Unknown error');

		$this->assertEquals(500, $res->getStatusCode());
		$this->assertEquals('Unknown error', $res->getReasonPhrase());
	}

	/**
	 * Method to test getReasonPhrase().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Response\Response::getReasonPhrase
	 */
	public function testGetReasonPhrase()
	{
		$res = new Response;

		$res = $res->withStatus(200);

		$this->assertEquals('OK', $res->getReasonPhrase());

		$res = $res->withStatus(400);

		$this->assertEquals('Bad Request', $res->getReasonPhrase());

		$res = $res->withStatus(404);

		$this->assertEquals('Not Found', $res->getReasonPhrase());

		$res = $res->withStatus(500);

		$this->assertEquals('Internal Server Error', $res->getReasonPhrase());
	}
}
