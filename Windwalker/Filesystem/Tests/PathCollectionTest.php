<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\Filesystem\Path;
use Windwalker\Filesystem\Path\PathLocator;
use Windwalker\Filesystem\Path\PathCollection;

/**
 * Tests for the PathCollection class.
 *
 * @since  1.0
 */
class PathCollectionTest extends PHPUnit_Framework_TestCase
{
	public $collection;
	
	/**
	 * setUp description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  setUpReturn
	 *
	 * @since  1.0
	 */
	public function setUp()
	{
		$this->collection = new PathCollection();
	}
	
	/**
	 * Data provider for testClean() method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPathData()
	{
		return array(
			// Input Path, Directory Separator, Expected Output
			'one path' => array(
				'/var/www/foo/bar',
				array(new PathLocator('/var/www/foo/bar'))
			),
			
			'paths with on key' => array(
				array(
					'/',
					'/var/www/foo/bar',
					'/var/www/joomla/bar/foo'
				),
				array(
					new PathLocator('/'),
					new PathLocator('/var/www/foo/bar'),
					new PathLocator('/var/www/joomla/bar/foo')
				)
			),
			
			'paths with key' => array(
				array(
					'root' => '/',
					'foo' => '/var/www/foo'
				),
				array(
					'root' => new PathLocator('/'),
					'foo' => new PathLocator('/var/www/foo')
				)
			)
		);
	}
	
	/**
	 * Data provider for testClean() method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getIteratorData()
	{
		return array(
			'no rescurive' => array(
				array(
					__DIR__ . '/files/folder1',
					__DIR__ . '/files/folder2'
				),
				
				array(
					Path::clean(__DIR__ . '/files/folder1'),
					Path::clean(__DIR__ . '/files/folder2')
				),
				
				false
			),
			/*
			'rescurive' => array(
				array(
					__DIR__ . 'files'
				),
				
				array(
					Path::clean(__DIR__ . 'files/folder1'),
					Path::clean(__DIR__ . 'files/folder1/file1'),
					Path::clean(__DIR__ . 'files/folder2/file2.html'),
					Path::clean(__DIR__ . 'files/file2.txt')
				),
				
				true
			)
			*/
		);
	}
	
	/**
	 * name description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  nameReturn
	 *
	 * @since  1.0
	 */
	public function getIteratorRecursiveData()
	{
		return array(
			'no rescurive' => array(
				array(
					__DIR__ . '/files/folder1',
					__DIR__ . '/files/folder2',
					__DIR__ . '/files/file2.txt',
				),
				
				array(
					Path::clean(__DIR__ . '/files/folder1'),
					Path::clean(__DIR__ . '/files/folder2')
				),
				
				false
			),
			
			'rescurive' => array(
				array(
					__DIR__ . '/files'
				),
				
				array(
					Path::clean(__DIR__ . '/files/folder1'),
					Path::clean(__DIR__ . '/files/folder1/path1'),
					Path::clean(__DIR__ . '/files/folder2/file2.html'),
					Path::clean(__DIR__ . '/files/file2.txt')
				),
				
				true
			)
		);
	}
	
	/**
	 * test__construct description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  test__constructReturn
	 *
	 * @since  1.0
	 */
	public function test__construct()
	{
		$collections = new PathCollection('/var/www/foo/bar');
		
		$paths = $collections->getPaths();
		
		$this->assertEquals(array(new PathLocator('/var/www/foo/bar')), $paths);
	}
	
	/**
	 * testAddPaths description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  testAddPathsReturn
	 *
	 * @dataProvider  getPathData
	 * 
	 * @since  1.0
	 */
	public function testAddPaths($paths, $expects)
	{
		$this->collection->addPaths($paths);
		
		$paths = $this->collection->getPaths();
		
		$this->assertEquals($paths, $expects);
	}
	
	/**
	 * addPath description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  addPathReturn
	 *
	 * @since  1.0
	 */
	public function testAddPath()
	{
		$path = new PathLocator('/var/foo/bar');
		
		$this->collection->addPath($path, 'bar');
		
		$this->assertEquals($path, $this->collection->getPath('bar'));
	}
	
	/**
	 * removePath description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  removePathReturn
	 *
	 * @since  1.0
	 */
	public function testRemovePath()
	{
		$path = new PathLocator('/var/foo/bar');
		
		$this->collection->addPath($path, 'bar');
		
		$this->collection->removePath('bar');
		
		$path = $this->collection->getPath('bar');
		
		$this->assertNull($path);
	}
	
	/**
	 * getPaths description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  getPathsReturn
	 *
	 * @dataProvider  getPathData
	 * 
	 * @since  1.0
	 */
	public function testGetPaths($paths, $expects)
	{
		$this->setUp();
		
		$this->collection->addPaths($paths);
		
		$paths = $this->collection->getPaths();
		
		$this->assertEquals($paths, $expects);
	}
	
	/**
	 * getPath description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  getPathReturn
	 *
	 * @since  1.0
	 */
	public function testGetPath()
	{
		$path = new PathLocator('/var/foo/bar2');
		
		$this->collection->addPath($path, 'bar2');
		
		$this->assertEquals($path, $this->collection->getPath('bar2'));
		
		$this->assertEquals(new PathLocator('/'), $this->collection->getPath('bar3', '/'));
	}
	
	/**
	 * getIterator description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  getIteratorReturn
	 *
	 * @dataProvider  getIteratorData
	 *
	 * @since  1.0
	 */
	public function testGetIterator($paths, $expects, $rescursive)
	{
		$this->setUp();
		
		$this->collection->addPaths($paths);
		
		$iterator = $this->collection;
		
		$compare = array();
		
		foreach($iterator as $file)
		{
			$compare[] = (string) $file;
		}
		
		$this->assertEquals($compare, $expects);
	}
	
	/**
	 * testDiresctoryIterator description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  testDiresctoryIteratorReturn
	 *
	 * @since  1.0
	 */
	public function testGetDiresctoryIterator()
	{
		
	}
	
	/**
	 * setPrefix description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  setPrefixReturn
	 *
	 * @since  1.0
	 */
	public function testSetPrefix()
	{
		$this->setUp();
		
		$this->collection->addPath('joomla/dir/foo/bar', 'foo');
		$this->collection->addPath('joomla/dir/yoo/hoo', 'yoo');
		
		$this->collection->setPrefix('/var/www');
		
		$expects = array(
			Path::clean('/var/www/joomla/dir/foo/bar'),
			Path::clean('/var/www/joomla/dir/yoo/hoo'),
		);
		
		$paths = array(
			(string) $this->collection->getPath('foo'),
			(string) $this->collection->getPath('yoo')
		);
		
		$this->assertEquals($paths, $expects);
	}
	
	/**
	 * find description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  findReturn
	 *
	 * @since  1.0
	 */
	public function find()
	{
		
	}
	
	/**
	 * findAll description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  findAllReturn
	 *
	 * @since  1.0
	 */
	public function findAll()
	{
		
	}
	
	/**
	 * toArray description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  toArrayReturn
	 *
	 * @since  1.0
	 */
	public function toArray()
	{
		
	}
	
	/**
	 * getFiles description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  getFilesReturn
	 *
	 * @since  1.0
	 */
	public function getFiles($rescursive = false)
	{
		
	}
	
	/**
	 * getFolders description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  getFoldersReturn
	 *
	 * @since  1.0
	 */
	public function getFolders($rescursive)
	{
		
	}
	
	/**
	 * appendAll description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  appendAllReturn
	 *
	 * @since  1.0
	 */
	public function appendAll()
	{
		
	}
	
	/**
	 * prependAll description
	 *
	 * @param  string
	 * @param  string
	 * @param  string
	 *
	 * @return  string  prependAllReturn
	 *
	 * @since  1.0
	 */
	public function prependAll()
	{
		
	}
}