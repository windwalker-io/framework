<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Stream;

use Windwalker\Http\Stream\Stream;
use Windwalker\Test\TestCase\AbstractBaseTestCase;
use Windwalker\Test\TestHelper;

/**
 * Test class of Stream
 *
 * @since 2.1
 */
class StreamTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var \Windwalker\Http\Stream\Stream
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
		$this->instance = new Stream('php://memory', Stream::MODE_READ_WRITE_RESET);
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

	public function testConstruct()
	{
		$resource = fopen('php://memory', Stream::MODE_READ_WRITE_RESET);
		$stream   = new Stream($resource);

		$this->assertInstanceOf('Windwalker\Http\Stream\Stream', $stream);

		$stream = new Stream;

		$this->assertInternalType('resource', TestHelper::getValue($stream, 'resource'));
		$this->assertEquals('php://memory',  TestHelper::getValue($stream, 'stream'));
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::__toString
	 */
	public function test__toString()
	{
		$message = 'foo bar';

		$this->instance->write($message);

		$this->assertEquals($message, (string) $this->instance);

		// Not readable should return empty string
		$this->createTempFile();

		file_put_contents($this->tmpnam, 'FOO BAR');

		$stream = new Stream($this->tmpnam, 'w');

		$this->assertEquals('', $stream->__toString());
	}

	/**
	 * Method to test close().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::close
	 */
	public function testClose()
	{
		$this->createTempFile();

		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_RESET);

		$stream = new Stream($resource);
		$stream->write('Foo Bar');

		$stream->close();

		$this->assertFalse(is_resource($resource));
		$this->assertAttributeEmpty('resource', $stream);
		$this->assertEquals('', (string) $stream);
	}

	/**
	 * Method to test detach().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::detach
	 */
	public function testDetach()
	{
		$resource = fopen('php://memory', Stream::MODE_READ_WRITE_RESET);
		$stream   = new Stream($resource);

		$this->assertSame($resource, $stream->detach());
		$this->assertAttributeEmpty('resource', $stream);
		$this->assertAttributeEmpty('stream', $stream);
	}

	/**
	 * Method to test getSize().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::getSize
	 */
	public function testGetSize()
	{
		$this->createTempFile();

		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_ONLY_FROM_BEGIN);

		$stream = new Stream($resource);

		$this->assertEquals(7, $stream->getSize());
	}

	/**
	 * Method to test tell().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::tell
	 */
	public function testTell()
	{
		$this->createTempFile();

		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_RESET);

		$stream = new Stream($resource);

		fseek($resource, 2);

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
	 * @covers \Windwalker\Http\Stream\Stream::eof
	 */
	public function testEof()
	{
		$this->createTempFile();
		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_ONLY_FROM_BEGIN);
		$stream = new Stream($resource);

		fseek($resource, 2);
		$this->assertFalse($stream->eof());

		while (! feof($resource))
		{
			fread($resource, 4096);
		}

		$this->assertTrue($stream->eof());

		$stream->rewind();
		$stream->read(8);

		$this->assertTrue($stream->eof());

		$stream->rewind();
		$stream->seek(8);
		$this->assertFalse($stream->eof());
		$stream->read(1);
		$this->assertTrue($stream->eof());
	}

	/**
	 * Method to test isSeekable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::isSeekable
	 */
	public function testIsSeekable()
	{
		$this->createTempFile();

		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_RESET);
		$stream = new Stream($resource);

		$this->assertTrue($stream->isSeekable());
	}

	/**
	 * Method to test seek().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::seek
	 */
	public function testSeek()
	{
		$this->createTempFile();
		file_put_contents($this->tmpnam, 'FOO BAR');

		$resource = fopen($this->tmpnam, Stream::MODE_READ_ONLY_FROM_BEGIN);
		$stream = new Stream($resource);

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
	 * @covers \Windwalker\Http\Stream\Stream::rewind
	 */
	public function testRewind()
	{
		$this->createTempFile();
		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_RESET);

		$stream = new Stream($resource);

		$this->assertTrue($stream->seek(2));

		$stream->rewind();

		$this->assertEquals(0, $stream->tell());
	}

	/**
	 * Method to test isWritable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::isWritable
	 */
	public function testIsWritable()
	{
		$stream = new Stream('php://memory', Stream::MODE_READ_ONLY_FROM_BEGIN);

		$this->assertFalse($stream->isWritable());
	}

	/**
	 * Method to test write().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::write
	 */
	public function testWrite()
	{
		$this->createTempFile();
		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_RESET);

		$stream = new Stream($resource);
		$stream->write('flower');

		$this->assertEquals('flower', file_get_contents($this->tmpnam));

		$stream->write(' bloom');

		$this->assertEquals('flower bloom', file_get_contents($this->tmpnam));

		$stream->seek(4);

		$stream->write('test');

		$this->assertEquals('flowtestloom', file_get_contents($this->tmpnam));
	}

	/**
	 * Method to test isReadable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::isReadable
	 */
	public function testIsReadable()
	{
		$this->createTempFile();

		$stream = new Stream($this->tmpnam, Stream::MODE_WRITE_ONLY_RESET);
		$this->assertFalse($stream->isReadable());
	}

	/**
	 * Method to test read().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::read
	 */
	public function testRead()
	{
		$this->createTempFile();
		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_ONLY_FROM_BEGIN);

		$stream = new Stream($resource);

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
	 * @covers \Windwalker\Http\Stream\Stream::getContents
	 */
	public function testGetContents()
	{
		$this->createTempFile();
		file_put_contents($this->tmpnam, 'FOO BAR');
		$resource = fopen($this->tmpnam, Stream::MODE_READ_ONLY_FROM_BEGIN);

		$stream = new Stream($resource);

		$this->assertEquals('FOO BAR', $stream->getContents());
	}

	/**
	 * Method to test getMetadata().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Stream\Stream::getMetadata
	 */
	public function testGetMetadata()
	{
		$this->createTempFile();
		$resource = fopen($this->tmpnam, Stream::MODE_READ_WRITE_FROM_BEGIN);
		$this->instance->attach($resource);

		$this->assertEquals(stream_get_meta_data($resource), $this->instance->getMetadata());

		$this->assertEquals(Stream::MODE_READ_WRITE_FROM_BEGIN, $this->instance->getMetadata('mode'));

		fclose($resource);
	}

	/**
	 * createTempFile
	 *
	 * @return  string
	 */
	protected function createTempFile()
	{
		return $this->tmpnam = tempnam(sys_get_temp_dir(), 'windwalker');
	}
}
