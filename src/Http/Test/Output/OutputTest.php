<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Output;

use Windwalker\Http\Response\TextResponse;
use Windwalker\Http\Test\Stub\StubOutput;

/**
 * Test class of Output
 *
 * @since 3.0
 */
class OutputTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubOutput
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
		$this->instance = new StubOutput;
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
	 * Method to test respond().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\Output::respond
	 */
	public function testRespond()
	{
		$this->instance->headerSent = function ()
		{
		    return false;
		};

		// Test return body
		$return = (string) $this->instance->respond(new TextResponse('Flower', 256, array('x-foo' => 'bar')), true)->getBody();

		$this->assertEquals('Flower', (string) $return);

		$this->assertEquals(array('bar'), $this->instance->message->getHeader('x-foo'));
		$this->assertEquals('HTTP/1.1 256', $this->instance->status);

		// Test respond instantly
		ob_start();

		$this->instance->respond(new TextResponse('Flower', 256, array('x-foo' => 'bar')));

		$content = ob_get_clean();

		$this->assertEquals('Flower', $content);
	}

	/**
	 * Method to test sendBody().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\Output::sendBody
	 */
	public function testSendBody()
	{
		ob_start();

		$this->instance->sendBody(new TextResponse('Flower'));

		$content = ob_get_clean();

		$this->assertEquals('Flower', $content);
	}

	/**
	 * Method to test header().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\Output::header
	 */
	public function testHeader()
	{
		// Test header send
		$this->instance->header('location: http://windwalker.io');
		
		// Should auto convert string case
		$this->assertEquals(array('http://windwalker.io'), $this->instance->message->getHeader('Location'));
		
		// Test replace
		$this->instance->header('x-foo: bar');
		$this->instance->header('x-foo: baz');
		$this->instance->header('x-foo: yoo', true);

		$this->assertEquals(array('yoo'), $this->instance->message->getHeader('x-foo'));
	}

	/**
	 * Method to test sendHeaders().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\Output::sendHeaders
	 */
	public function testSendHeaders()
	{
		$this->instance->sendHeaders(new TextResponse('Flower', 256, array(
			'x-foo' => 'bar',
			'x-flower' => array(
				'sakura',
				'rose',
				'olive'
			)
		)));

		$expected = array(
			'X-Foo' => array('bar'),
			'X-Flower' => array(
				'sakura',
				'rose',
				'olive'
			),
			'Content-Type' => array('text/plain; charset=utf-8')
		);

		$this->assertEquals($expected, $this->instance->message->getHeaders());
	}

	/**
	 * Method to test sendStatusLine().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\Output::sendStatusLine
	 */
	public function testSendStatusLine()
	{
		$response = new TextResponse('Flower', 256);

		$this->instance->sendStatusLine($response);

		$this->assertEquals('HTTP/1.1 256', $this->instance->status);

		$response = $response->withProtocolVersion('2');
		$response = $response->withStatus(123);

		$this->instance->sendStatusLine($response);

		$this->assertEquals('HTTP/2 123', $this->instance->status);
	}
}
