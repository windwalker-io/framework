<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filesystem\Test;

use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Folder;
use Windwalker\Filesystem\Path;
use Windwalker\Test\TestEnvironment;

/**
 * Test class of Folder
 *
 * @since 2.0
 */
class FolderTest extends AbstractFilesystemTest
{
	/**
	 * Method to test copy().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::copy
	 */
	public function testCopy()
	{
		Folder::delete(static::$dest);

		Folder::copy(static::$src, static::$dest);

		$this->assertTrue(is_dir(static::$dest));
		$this->assertFileExists(static::$dest . '/folder1/level2/file3');
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::create
	 */
	public function testCreate()
	{
		Folder::create(static::$dest . '/flower');

		$this->assertTrue(is_dir(static::$dest . '/flower'));

		Folder::create(static::$dest . '/foo/bar');

		$this->assertTrue(is_dir(static::$dest . '/foo/bar'));

		Folder::create(static::$dest . '/yoo', 0775);

		if (TestEnvironment::isWindows())
		{
			$this->assertEquals(777, Path::getPermissions(static::$dest . '/yoo'));
		}
		else
		{
			$this->assertEquals(775, Path::getPermissions(static::$dest . '/yoo'));
		}
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::delete
	 */
	public function testDelete()
	{
		Folder::create(static::$dest . '/flower');

		Folder::delete(static::$dest . '/flower');

		$this->assertFalse(is_dir(static::$dest . '/flower'));
	}

	/**
	 * Method to test move().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::move
	 */
	public function testMove()
	{
		$dest2 = __DIR__ . '/dest2';

		if (is_dir($dest2))
		{
			Folder::delete($dest2);
		}

		Folder::move(static::$dest, $dest2);

		$this->assertTrue(is_dir($dest2));
		$this->assertFileExists($dest2 . '/folder1/level2/file3');

		Folder::delete($dest2);
	}

	/**
	 * Method to test files().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::files
	 */
	public function testFiles()
	{
		$files = Folder::files(__DIR__ . '/dest/folder1/level2', true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(__DIR__ . '/dest/folder1/level2/file3')),
			FilesystemTestHelper::cleanPaths($files)
		);

		// No full name
		$files = Folder::files(__DIR__ . '/dest/folder1/level2', true, Folder::PATH_BASENAME);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array('file3')),
			FilesystemTestHelper::cleanPaths($files)
		);

		$files = Folder::files(__DIR__ . '/dest/folder1', true, Folder::PATH_RELATIVE);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array('level2/file3', 'path1')),
			FilesystemTestHelper::cleanPaths($files)
		);

		// Recursive
		$files = Folder::files(static::$dest, true);

		$compare = FilesystemTestHelper::getFilesRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($files)
		);
	}

	/**
	 * Method to test items().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::items
	 */
	public function testItems()
	{
		$items = Folder::items(static::$dest . '/folder1/level2', true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(static::$dest . '/folder1/level2/file3')),
			FilesystemTestHelper::cleanPaths($items)
		);

		// No full name
		$items = Folder::items(static::$dest . '/folder1/level2', true, Folder::PATH_BASENAME);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array('file3')),
			FilesystemTestHelper::cleanPaths($items)
		);

		// Recursive
		$items = Folder::items(static::$dest, true, Folder::PATH_ABSOLUTE);

		$compare = FilesystemTestHelper::getItemsRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($items)
		);
	}

	/**
	 * Method to test folders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::folders
	 */
	public function testFolders()
	{
		$folders = Folder::folders(static::$dest . '/folder1', true);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array(static::$dest . '/folder1/level2')),
			FilesystemTestHelper::cleanPaths($folders)
		);

		// No full name
		$folders = Folder::folders(static::$dest . '/folder1', true, Folder::PATH_BASENAME);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array('level2')),
			FilesystemTestHelper::cleanPaths($folders)
		);

		$folders = Folder::folders(static::$dest, true, Folder::PATH_RELATIVE);

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths(array('folder1', 'folder1/level2', 'folder2')),
			FilesystemTestHelper::cleanPaths($folders)
		);

		// Recursive
		$folders = Folder::folders(static::$dest, true, Folder::PATH_ABSOLUTE);

		$compare = FilesystemTestHelper::getFoldersRecursive('dest');

		$this->assertEquals(
			FilesystemTestHelper::cleanPaths($compare),
			FilesystemTestHelper::cleanPaths($folders)
		);
	}

	/**
	 * Method to test listFolderTree().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::listFolderTree
	 */
	public function testListFolderTree()
	{
		$tree = Folder::listFolderTree(static::$dest);

		$this->assertEquals($tree[0]['relative'], 'folder1');
		$this->assertEquals($tree[1]['parent'], 1);
		$this->assertEquals($tree[1]['relative'], 'folder1' . DIRECTORY_SEPARATOR . 'level2');
		$this->assertEquals($tree[2]['fullname'], Path::clean(static::$dest . '/folder2'));
	}

	/**
	 * Method to test makeSafe().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Filesystem\Folder::makeSafe
	 */
	public function testMakeSafe()
	{
		$safe = Folder::makeSafe('fo_o/bar 2/yo-o.o/三杯雞 go:to:fly.ing');

		$this->assertEquals('fo_o/bar2/yo-o.o/gotofly.ing', $safe);
	}
}
