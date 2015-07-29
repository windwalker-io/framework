<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Path;

/**
 * Test class of Filesystem
 *
 * @since 2.0
 */
class FilesystemTest extends AbstractFilesystemTest
{
	/**
	 * Method to test copy().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::copy
	 */
	public function testCopy()
	{
		Filesystem::delete(static::$dest);

		Filesystem::copy(static::$src, static::$dest);

		$this->assertTrue(is_dir(static::$dest));
		$this->assertFileExists(__DIR__ . '/dest/folder1/level2/file3');
	}

	/**
	 * Method to test move().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::move
	 */
	public function testMove()
	{
		$dest2 = __DIR__ . '/dest2';

		if (is_dir($dest2))
		{
			Filesystem::delete($dest2);
		}

		Filesystem::move(static::$dest, $dest2);

		$this->assertTrue(is_dir($dest2));
		$this->assertFileExists($dest2 . '/folder1/level2/file3');

		Filesystem::delete($dest2);
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::delete
	 */
	public function testDelete()
	{
		Filesystem::delete(static::$dest);

		$this->assertFalse(is_dir(static::$dest));
		$this->assertFileNotExists(static::$dest . '/folder1/level2/file3');
	}

	/**
	 * Method to test files().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::files
	 */
	public function testFiles()
	{
		$files = Filesystem::files(__DIR__ . '/dest/folder1/level2', true, true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(__DIR__ . '/dest/folder1/level2/file3')),
			FilesystemTestHelper::cleanPaths($files)
		);

		// Recursive
		$files = Filesystem::files(static::$dest, true, true);

		$compare = FilesystemTestHelper::getFilesRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($files)
		);

		// Iterator
		$files = Filesystem::files(static::$dest, true);

		$this->assertInstanceOf('CallbackFilterIterator', $files);

		$files2 = Filesystem::iteratorToArray($files);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($files2)
		);

		$files->rewind();

		$this->assertInstanceOf('SplFileinfo', $files->current());
	}

	/**
	 * Method to test folders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::folders
	 */
	public function testFolders()
	{
		$folders = Filesystem::folders(static::$dest . '/folder1', true, true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(static::$dest . '/folder1/level2')),
			FilesystemTestHelper::cleanPaths($folders)
		);

		// Recursive
		$folders = Filesystem::folders(static::$dest, true, true);

		$compare = FilesystemTestHelper::getFoldersRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($folders)
		);

		// Iterator
		$folders = Filesystem::folders(static::$dest, true);

		$this->assertInstanceOf('CallbackFilterIterator', $folders);

		$folders2 = Filesystem::iteratorToArray($folders);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($folders2)
		);

		$folders->rewind();

		$this->assertInstanceOf('SplFileinfo', $folders->current());
	}

	/**
	 * Method to test items().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::items
	 */
	public function testItems()
	{
		$items = Filesystem::items(static::$dest . '/folder1/level2', true, true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(static::$dest . '/folder1/level2/file3')),
			FilesystemTestHelper::cleanPaths($items)
		);

		// Recursive
		$items = Filesystem::items(static::$dest, true, true);

		$compare = FilesystemTestHelper::getItemsRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($items)
		);

		// Iterator
		$items = Filesystem::items(static::$dest, true);

		$this->assertInstanceOf('CallbackFilterIterator', $items);

		$items2 = Filesystem::iteratorToArray($items);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($items2)
		);

		$items->rewind();

		$this->assertInstanceOf('SplFileinfo', $items->current());
	}

	/**
	 * Method to test findOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::findOne
	 */
	public function testFindOne()
	{
		// String condition
		$file = Filesystem::findOne(static::$dest, 'file', true);
		$files = Filesystem::find(static::$dest, 'file', true, true);

		$this->assertEquals(
			Path::clean((string) $files[0]),
			Path::clean((string) $file)
		);
	}

	/**
	 * Method to test find().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::find
	 */
	public function testFind()
	{
		// String condition
		$files = Filesystem::find(static::$dest, 'file', true, true);

		$expect1 = array(
			__DIR__ . '/dest/file2.txt',
			__DIR__ . '/dest/folder1/level2/file3',
			__DIR__ . '/dest/folder2/file2.html',
		);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($expect1),
			FilesystemTestHelper::cleanPaths($files)
		);

		// Array condition
		$files = Filesystem::find(static::$dest, array('file', 'path'), true, true);

		$expect2 = array(
			__DIR__ . '/dest/file2.txt',
			__DIR__ . '/dest/folder1/level2/file3',
			__DIR__ . '/dest/folder1/path1',
			__DIR__ . '/dest/folder2/file2.html',
		);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($expect2),
			FilesystemTestHelper::cleanPaths($files)
		);

		// Callable condition
		$condition = function ($current, $key, $iterator)
		{
			return pathinfo($current->getBasename(), PATHINFO_EXTENSION) == 'html';
		};

		$files = Filesystem::find(static::$dest, $condition, true, true);

		$expect3 = array(
			__DIR__ . '/dest/folder2/file2.html',
		);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($expect3),
			FilesystemTestHelper::cleanPaths($files)
		);
	}

	/**
	 * Method to test findByCallback().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::findByCallback
	 */
	public function testFindByCallback()
	{
		$condition = function ($current, $key, $iterator)
		{
			return pathinfo($current->getBasename(), PATHINFO_EXTENSION) == 'html';
		};

		$files = Filesystem::find(static::$dest, $condition, true, true);

		$expect3 = array(
			__DIR__ . '/dest/folder2/file2.html',
		);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($expect3),
			FilesystemTestHelper::cleanPaths($files)
		);
	}

	/**
	 * Method to test createIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Filesystem::createIterator
	 */
	public function testCreateIterator()
	{
		$this->assertInstanceOf('Windwalker\\Filesystem\\Iterator\\RecursiveDirectoryIterator', Filesystem::createIterator(static::$dest));
		$this->assertInstanceOf('RecursiveIteratorIterator', Filesystem::createIterator(static::$dest, true));
	}
}
