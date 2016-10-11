<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Stream;

use Windwalker\Http\Stream\Stream;
use Windwalker\Http\Stream\StringStream;
use Windwalker\Test\TestCase\AbstractBaseTestCase;
use Windwalker\Test\TestHelper;

/**
 * Test class of Stream
 *
 * @since 2.1
 */
class StringStreamTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var \Windwalker\Http\Stream\StringStream
	 */
	protected $instance;

	/**
	 * Property tmp.
	 *
	 * @var  string
	 */
	public $tmpnam;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new StringStream('', Stream::MODE_READ_WRITE_FROM_BEGIN);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		if ($this->tmpnam && is_file($this->tmpnam))
		{
			unlink($this->tmpnam);
		}
	}

	/**
	 * testConstruct
	 *
	 * @return  void
	 */
	public function testConstruct()
	{
		$stringObject = $this->getMockBuilder('stdClass')
			->setMethods(array('__toString'))
			->getMock();

		$stringObject->expects($this->once())
			->method('__toString')
			->willReturn('FOO');

		$stream = new StringStream($stringObject);

		$this->assertEquals('FOO', TestHelper::getValue($stream, 'resource'));
		$this->assertInternalType('object',  TestHelper::getValue($stream, 'stream'));
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::__toString
	 */
	public function test__toString()
	{
		$message = 'foo bar';

		$this->instance->write($message);

		$this->assertEquals($message, (string) $this->instance);
	}

	/**
	 * Method to test close().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::close
	 */
	public function testClose()
	{
		$stream = new StringStream;
		$stream->write('Foo Bar');

		$stream->close();

		$this->assertEmpty($stream->getResource());
		$this->assertAttributeEmpty('resource', $stream);
		$this->assertEquals('', (string) $stream);
	}

	/**
	 * Method to test detach().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::detach
	 */
	public function testDetach()
	{
		$stream = new StringStream('flower');

		$this->assertEquals('flower', $stream->detach());
		$this->assertAttributeEmpty('resource', $stream);
		$this->assertAttributeEmpty('stream', $stream);
	}

	/**
	 * Method to test getSize().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::getSize
	 */
	public function testGetSize()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertEquals(7, $stream->getSize());
	}

	/**
	 * Method to test tell().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::tell
	 */
	public function testTell()
	{
		$stream = new StringStream('FOO BAR');

		$stream->seek(2);

		$this->assertEquals(2, $stream->tell());

		$stream->detach();

		$this->assertExpectedException(function() use ($stream)
		{
			$stream->tell();
		}, new \RuntimeException);
	}

	/**
	 * Method to test eof().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::eof
	 */
	public function testEof()
	{
		$stream = new StringStream('FOO BAR');

		$stream->read(2);
		$this->assertFalse($stream->eof());

		$stream->rewind();
		$stream->read(8);
		$this->assertTrue($stream->eof());

		$stream->rewind();
		$stream->seek(8);
		$this->assertFalse($stream->eof());
		$stream->read(1);
		$this->assertTrue($stream->eof());

		$stream->rewind();
		$stream->read(4096);
		$this->assertTrue($stream->eof());
	}

	/**
	 * Method to test isSeekable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::isSeekable
	 */
	public function testIsSeekable()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertTrue($stream->isSeekable());

		$stream->seekable(false);

		$this->assertFalse($stream->isSeekable());
	}

	/**
	 * Method to test seek().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::seek
	 */
	public function testSeek()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertTrue($stream->seek(2));
		$this->assertEquals(2, $stream->tell());

		$this->assertTrue($stream->seek(2, SEEK_CUR));
		$this->assertEquals(4, $stream->tell());

		$this->assertTrue($stream->seek(-1, SEEK_END));
		$this->assertEquals(6, $stream->tell());
	}

	/**
	 * Method to test rewind().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::rewind
	 */
	public function testRewind()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertTrue($stream->seek(2));

		$stream->rewind();

		$this->assertEquals(0, $stream->tell());
	}

	/**
	 * Method to test isWritable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::isWritable
	 */
	public function testIsWritable()
	{
		$stream = new StringStream('php://memory', Stream::MODE_READ_ONLY_FROM_BEGIN);

		$this->assertFalse($stream->isWritable());
	}

	/**
	 * Method to test write().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::write
	 */
	public function testWrite()
	{
		$stream = new StringStream('');
		$stream->write('flower');

		$this->assertEquals('flower', $stream->getResource());

		$stream->write(' bloom');

		$this->assertEquals('flower bloom', $stream->getResource());

		$stream->seek(4);

		$stream->write('test');

		$this->assertEquals('flowtestloom', $stream->getResource());
	}

	/**
	 * Method to test isReadable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::isReadable
	 */
	public function testIsReadable()
	{
		$stream = new StringStream;
		$this->assertTrue($stream->isReadable());
	}

	/**
	 * Method to test read().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::read
	 */
	public function testRead()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertEquals('FO', $stream->read(2));
		$this->assertEquals('O B', $stream->read(3));

		$stream->rewind();

		$this->assertEquals('FOO', $stream->read(3));
	}

	/**
	 * Method to test getContents().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::getContents
	 */
	public function testGetContents()
	{
		$stream = new StringStream('FOO BAR');

		$this->assertEquals('FOO BAR', $stream->getContents());

		$stream = new StringStream('FOO BAR');

		$stream->seek(2);

		$this->assertEquals('O BAR', $stream->getContents());
	}

	/**
	 * Method to test getMetadata().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\StringStream::getMetadata
	 */
	public function testGetMetadata()
	{
		$this->assertInternalType('array', $this->instance->getMetadata());

		$this->assertTrue($this->instance->getMetadata('seekable'));
		$this->assertEquals('rb', $this->instance->getMetadata('mode'));
	}
}
