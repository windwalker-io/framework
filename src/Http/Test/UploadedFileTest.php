<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test;

use Windwalker\Http\Stream\Stream;
use Windwalker\Http\UploadedFile;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of UploadedFile
 *
 * @since 2.1
 */
class UploadedFileTest extends AbstractBaseTestCase
{
	/**
	 * Property tmpFile.
	 *
	 * @var  string
	 */
	protected $tmpFile;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		if (is_scalar($this->tmpFile) && file_exists($this->tmpFile))
		{
			unlink($this->tmpFile);
		}
	}

	/**
	 * Method to test getStream().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::getStream
	 * @TODO   Implement testGetStream().
	 */
	public function testGetStream()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test moveTo().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::moveTo
	 */
	public function testMoveTo()
	{
		$stream = new Stream('php://temp', 'wb+');
		$stream->write('Foo bar!');
		$upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

		$this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

		$this->assertFalse(is_file($to));

		$upload->moveTo($to);

		$this->assertTrue(is_file($to));

		$contents = file_get_contents($to);

		$this->assertEquals($stream->__toString(), $contents);

		// Send string
		$uploadFile = sys_get_temp_dir() . '/upload-' . uniqid();
		file_put_contents($uploadFile, 'Foo bar!');
		$upload = new UploadedFile($uploadFile, 0, UPLOAD_ERR_OK);

		$this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

		$this->assertFalse(is_file($to));

		$upload->moveTo($to);

		$this->assertTrue(is_file($to));

		$contents = file_get_contents($to);

		$this->assertEquals('Foo bar!', $contents);

		@unlink($uploadFile);
	}

	/**
	 * testMoveInNotCli
	 *
	 * @return  void
	 */
	public function testMoveInNotCli()
	{
		// Send stream
		$stream = new Stream('php://temp', 'wb+');
		$stream->write('Foo bar!');
		$upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
		$upload->setSapi('cgi');

		$this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

		$this->assertFalse(is_file($to));

		$upload->moveTo($to);

		$this->assertTrue(is_file($to));

		$contents = file_get_contents($to);

		$this->assertEquals($stream->__toString(), $contents);

		// Send string
		$uploadFile = sys_get_temp_dir() . '/upload-' . uniqid();
		file_put_contents($uploadFile, 'Foo bar!');
		$upload = new UploadedFile($uploadFile, 0, UPLOAD_ERR_OK);
		$upload->setSapi('cgi');

		$this->tmpFile = $to = sys_get_temp_dir() . '/windwalker-' . uniqid();

		$this->assertFalse(is_file($to));

		$this->assertExpectedException(function() use ($upload, $to)
		{
			$upload->moveTo($to);
		}, 'RuntimeException', 'Error moving uploaded file');
	}

	/**
	 * Method to test getSize().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::getSize
	 * @TODO   Implement testGetSize().
	 */
	public function testGetSize()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getError().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::getError
	 * @TODO   Implement testGetError().
	 */
	public function testGetError()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getClientFilename().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::getClientFilename
	 * @TODO   Implement testGetClientFilename().
	 */
	public function testGetClientFilename()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getClientMediaType().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\UploadedFile::getClientMediaType
	 * @TODO   Implement testGetClientMediaType().
	 */
	public function testGetClientMediaType()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
