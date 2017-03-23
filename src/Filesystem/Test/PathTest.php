<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Path;

/**
 * Test class of Path
 *
 * @since 2.0
 */
class PathTest extends AbstractFilesystemTest
{
	/**
	 * Data provider for testClean() method.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function getCleanData()
	{
		return [
			// Input Path, Directory Separator, Expected Output
			'Nothing to do.' => ['/var/www/foo/bar/baz', '/', '/var/www/foo/bar/baz'],
			'One backslash.' => ['/var/www/foo\\bar/baz', '/', '/var/www/foo/bar/baz'],
			'Two and one backslashes.' => ['/var/www\\\\foo\\bar/baz', '/', '/var/www/foo/bar/baz'],
			'Mixed backslashes and double forward slashes.' => ['/var\\/www//foo\\bar/baz', '/', '/var/www/foo/bar/baz'],
			'UNC path.' => ['\\\\www\\docroot', '\\', '\\\\www\\docroot'],
			'UNC path with forward slash.' => ['\\\\www/docroot', '\\', '\\\\www\\docroot'],
			'UNC path with UNIX directory separator.' => ['\\\\www/docroot', '/', '/www/docroot'],
		];
	}

	/**
	 * Method to test canChmod().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::canChmod
	 * @TODO   Implement testCanChmod().
	 */
	public function testCanChmod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setPermissions().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::setPermissions
	 * @TODO   Implement testSetPermissions().
	 */
	public function testSetPermissions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getPermissions().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::getPermissions
	 * @TODO   Implement testGetPermissions().
	 */
	public function testGetPermissions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test check().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::check
	 * @TODO   Implement testCheck().
	 */
	public function testCheck()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test clean().
	 *
	 * @param   string  $input
	 * @param   string  $ds
	 * @param   string  $expected
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::clean
	 *
	 * @dataProvider  getCleanData
	 */
	public function testClean($input, $ds, $expected)
	{
		$this->assertEquals(
			$expected,
			Path::clean($input, $ds)
		);
	}

	/**
	 * Method to test find().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Filesystem\Path::find
	 * @TODO   Implement testFind().
	 */
	public function testFind()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * testExistsInsensitive
	 *
	 * @param string $path
	 * @param bool   $sExists
	 * @param bool   $isExists
	 *
	 * @return void
	 * @dataProvider existsProvider
	 */
	public function testExists($path, $sExists, $isExists)
	{
		self::assertSame($sExists, Path::exists($path, true));
		self::assertSame($isExists, Path::exists($path, false));
	}

	/**
	 * existsProvider
	 *
	 * @return  array
	 */
	public function existsProvider()
	{
		return [
			[
				__DIR__ . '/case/Flower/saKura/test.txt',
				false,
				true
			],
			[
				__DIR__ . '/case/Flower/saKura/TEST.txt',
				true,
				true
			],
			[
				__DIR__ . '/case/Flower/sakura',
				false,
				true
			],
			[
				__DIR__ . '/case/Flower/Olive',
				false,
				false
			]
		];
	}

	/**
	 * testFixCase
	 *
	 * @return  void
	 */
	public function testFixCase()
	{
		$path = __DIR__ . '/case/Flower/saKura/test.txt';

		self::assertEquals(Path::clean(__DIR__ . '/case/Flower/saKura/TEST.txt'), Path::fixCase($path));
	}
}
