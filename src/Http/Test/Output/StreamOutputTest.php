<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Output;

use Windwalker\Http\Response\TextResponse;
use Windwalker\Http\Test\Stub\StubStreamOutput;

/**
 * Test class of StreamOutput
 *
 * @since 3.0
 */
class StreamOutputTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubStreamOutput
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
		$this->instance = new StubStreamOutput;
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
	 * @covers \Windwalker\Http\Output\StreamOutput::respond
	 */
	public function testRespond()
	{
		// Test respond instantly
		$this->instance->respond(new TextResponse('Flower', 256, array('x-foo' => 'bar')));

		$this->assertEquals('Flower', $this->instance->output);
		$this->assertEquals(array('bar'), $this->instance->message->getHeader('X-Foo'));
		$this->assertEquals(array('text/plain; charset=utf-8'), $this->instance->message->getHeader('content-type'));
		$this->assertEquals(array(6), $this->instance->message->getHeader('content-length'));
		
		// Test respond range
		$this->instance->respond(new TextResponse('Flower', 256, array('Content-Range' => 'bytes 1-4/3')));

		$this->assertEquals('lowe', $this->instance->output);

		// Test delay
		$this->instance->setMaxBufferLength(1);

		$this->instance->respond(new TextResponse('Flower'));

		$this->assertNull($this->instance->waiting);

		$this->instance->setDelay(1);

		$this->instance->respond(new TextResponse('Flower'));

		if (version_compare(PHP_VERSION, '5.5', '>='))
		{
			$this->assertEquals(7, $this->instance->waiting);
		}
		else
		{
			$this->assertEquals(6, $this->instance->waiting);
		}
	}

	/**
	 * Method to test sendBody().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\StreamOutput::sendBody
	 */
	public function testSendBody()
	{
		$this->instance->sendBody(new TextResponse('Flower', 256, array('x-foo' => 'bar')));

		$this->assertEquals('Flower', $this->instance->output);
	}

	/**
	 * Method to test getMaxBufferLength().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\StreamOutput::getMaxBufferLength
	 * @covers \Windwalker\Http\Output\StreamOutput::setMaxBufferLength
	 */
	public function testGetAndSetMaxBufferLength()
	{
		$this->assertEquals(8192, $this->instance->getMaxBufferLength());

		$this->instance->setMaxBufferLength(4096);

		$this->assertEquals(4096, $this->instance->getMaxBufferLength());
	}

	/**
	 * Method to test getDelay().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Output\StreamOutput::getDelay
	 * @covers \Windwalker\Http\Output\StreamOutput::setDelay
	 */
	public function testGetAndSetDelay()
	{
		$this->assertNull($this->instance->getDelay());

		$this->instance->setDelay(1000);

		$this->assertEquals(1000, $this->instance->getDelay());
	}
}
