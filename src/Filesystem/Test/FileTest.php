<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Filesystem\File;

/**
 * Test class of File
 *
 * @since 2.0
 */
class FileTest extends AbstractFilesystemTest
{
	/**
	 * Method to test stripExtension().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::stripExtension
	 */
	public function testStripExtension()
	{
		$name = File::stripExtension('Wu-la.la');

		$this->assertEquals('Wu-la', $name);

		$name = File::stripExtension(__DIR__ . '/Wu-la.la');

		$this->assertEquals(__DIR__ . '/Wu-la', $name);
	}

	/**
	 * Method to test getExtension().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::getExtension
	 */
	public function testGetExtension()
	{
		$ext = File::getExtension('Wu-la.la');

		$this->assertEquals('la', $ext);
	}

	/**
	 * Method to test getFilename().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::getFilename
	 */
	public function testGetFilename()
	{
		$name = File::getFilename(__DIR__ . '/Wu-la.la');

		$this->assertEquals('Wu-la.la', $name);
	}

	/**
	 * Provides the data to test the makeSafe method.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function dataTestMakeSafe()
	{
		return array(
			array(
				'windwalker.',
				array('#^\.#'),
				'windwalker',
				'There should be no fullstop on the end of a filename',
			),
			array(
				'Test w1ndwa1ker_5-1.html',
				array('#^\.#'),
				'Test w1ndwa1ker_5-1.html',
				'Alphanumeric symbols, dots, dashes, spaces and underscores should not be filtered',
			),
			array(
				'Test w1ndwa1ker_5-1.html',
				array('#^\.#', '/\s+/'),
				'Testw1ndwa1ker_5-1.html',
				'Using strip chars parameter here to strip all spaces',
			),
			array(
				'windwalker.php!.',
				array('#^\.#'),
				'windwalker.php',
				'Non-alphanumeric symbols should be filtered to avoid disguising file extensions',
			),
			array(
				'windwalker.php.!',
				array('#^\.#'),
				'windwalker.php',
				'Non-alphanumeric symbols should be filtered to avoid disguising file extensions',
			),
			array(
				'.gitignore',
				array(),
				'.gitignore',
				'Files starting with a fullstop should be allowed when strip chars parameter is empty',
			),
		);
	}

	/**
	 * Method to test makeSafe().
	 *
	 * @param   string  $name        The name of the file to test filtering of
	 * @param   array   $stripChars  Whether to filter spaces out the name or not
	 * @param   string  $expected    The expected safe file name
	 * @param   string  $message     The message to show on failure of test
	 *
	 * @return void
	 *
	 * @covers        Windwalker\Filesystem\File::makeSafe
	 *
	 * @dataProvider  dataTestMakeSafe
	 */
	public function testMakeSafe($name, $stripChars, $expected, $message)
	{
		$this->assertEquals(File::makeSafe($name, $stripChars), $expected, $message);
	}

	/**
	 * Method to test copy().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::copy
	 */
	public function testCopy()
	{
		File::copy(static::$dest . '/folder1/level2/file3', static::$dest . '/folder2/level2/file4');

		$this->assertFileExists(static::$dest . '/folder2/level2/file4');

		// Copy force
		File::copy(static::$dest . '/folder1/level2/file3', static::$dest . '/folder2/level2/file4', true);

		$this->assertFileExists(static::$dest . '/folder2/level2/file4');
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::delete
	 */
	public function testDelete()
	{
		File::delete(static::$dest . '/folder1/path1');

		$this->assertFileNotExists(static::$dest . '/folder1/path1');

		try
		{
			File::delete(static::$dest . '/folder1/path2');
		}
		catch (FilesystemException $e)
		{
			$this->assertInstanceOf('Windwalker\\Filesystem\\Exception\\FilesystemException', $e);
		}
	}

	/**
	 * Method to test move().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::move
	 */
	public function testMove()
	{
		File::move(static::$dest . '/folder1/path1', static::$dest . '/folder2/level3/path2');

		$this->assertFileExists(static::$dest . '/folder2/level3/path2');

		// Move force
		File::move(static::$dest . '/folder1/level2/file3', static::$dest . '/folder2/level3/path2', true);

		$this->assertFileExists(static::$dest . '/folder2/level3/path2');
	}

	/**
	 * Method to test write().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::write
	 */
	public function testWrite()
	{
		File::write(static::$dest . '/folder3/level2/test.txt', 'tmpFile');

		$this->assertStringEqualsFile(static::$dest . '/folder3/level2/test.txt', 'tmpFile');
	}

	/**
	 * Method to test upload().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\File::upload
	 */
	public function testUpload()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
