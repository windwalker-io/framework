<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Registry\Test;

use Windwalker\Profiler\Profiler;
use Windwalker\Registry\Registry;
use Windwalker\Registry\RegistryHelper;
use Windwalker\Registry\Test\Stubs\StubDumpable;

/**
 * Test class of RegistryHelper
 *
 * @since 2.1
 */
class RegistryHelperTest extends \PHPUnit_Framework_TestCase
{
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
	}

	/**
	 * Method to test isAssociativeArray().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::isAssociativeArray
	 */
	public function testIsAssociativeArray()
	{
		$this->assertFalse(RegistryHelper::isAssociativeArray(array('a', 'b')));

		$this->assertTrue(RegistryHelper::isAssociativeArray(array(1, 2, 'a' => 'b', 'c', 'd')));
	}

	/**
	 * Method to test toObject().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::toObject
	 */
	public function testToObject()
	{
		$data = RegistryHelper::toObject(array('foo' => 'bar'));

		$this->assertInternalType('object', $data);

		$this->assertEquals('bar', $data->foo);

		$data = RegistryHelper::toObject(array('foo' => 'bar'), 'ArrayObject');

		$this->assertInstanceOf('ArrayObject', $data);

		$data = RegistryHelper::toObject(array('foo' => array('bar' => 'baz')));

		$this->assertEquals('baz', $data->foo->bar);
	}

	/**
	 * Method to test getByPath().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::getByPath
	 */
	public function testGetByPath()
	{
		$data = array(
			'flower' => 'sakura',
			'olive' => 'peace',
			'pos1' => array(
				'sunflower' => 'love'
			),
			'pos2' => array(
				'cornflower' => 'elegant'
			),
			'array' => array(
				'A',
				'B',
				'C'
			)
		);

		$this->assertEquals('sakura', RegistryHelper::getByPath($data, 'flower'));
		$this->assertEquals('love', RegistryHelper::getByPath($data, 'pos1.sunflower'));
		$this->assertEquals('love', RegistryHelper::getByPath($data, 'pos1/sunflower', '/'));
		$this->assertEquals($data['array'], RegistryHelper::getByPath($data, 'array'));
		$this->assertNull(RegistryHelper::getByPath($data, 'not.exists'));
	}

	/**
	 * Method to test getByPath().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::getByPath
	 */
	public function testGetByPathWithObject()
	{
		$data = array(
			'flower' => 'sakura',
			'olive' => 'peace',
			'pos1' => (object) array(
				'sunflower' => 'love'
			),
			'pos2' => new Registry(array(
				'cornflower' => 'elegant'
			)),
			'array' => array(
				'A',
				'B',
				'C'
			)
		);

		$this->assertEquals('sakura', RegistryHelper::getByPath($data, 'flower'));
		$this->assertEquals('love', RegistryHelper::getByPath($data, 'pos1.sunflower'));
		$this->assertEquals('elegant', RegistryHelper::getByPath($data, 'pos2.cornflower'));
		$this->assertEquals(null, RegistryHelper::getByPath($data, 'pos2.data'));
	}

	/**
	 * Method to test setByPath().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::setByPath
	 */
	public function testSetByPath()
	{
		$data = array();

		// One level
		$return = RegistryHelper::setByPath($data, 'flower', 'sakura');

		$this->assertEquals('sakura', $data['flower']);
		$this->assertTrue($return);

		// Multi-level
		RegistryHelper::setByPath($data, 'foo.bar', 'test');

		$this->assertEquals('test', $data['foo']['bar']);

		// Separator
		RegistryHelper::setByPath($data, 'foo/bar', 'play', '/');

		$this->assertEquals('play', $data['foo']['bar']);

		// False
		$return = RegistryHelper::setByPath($data, '', 'goo');

		$this->assertFalse($return);

		// Fix path
		RegistryHelper::setByPath($data, 'double..separators', 'value');

		$this->assertEquals('value', $data['double']['separators']);
	}

	/**
	 * Method to test getPathNodes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\RegistryHelper::getPathNodes
	 */
	public function testGetPathNodes()
	{
		$this->assertEquals(array('a', 'b', 'c'), RegistryHelper::getPathNodes('a..b.c'));
		$this->assertEquals(array('a', 'b', 'c'), RegistryHelper::getPathNodes('a//b/c', '/'));
	}

	/**
	 * testFlatten
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Registry\RegistryHelper::flatten
	 * @since   2.0
	 */
	public function testFlatten()
	{
		$array = array(
			'flower' => 'sakura',
			'olive' => 'peace',
			'pos1' => array(
				'sunflower' => 'love'
			),
			'pos2' => array(
				'cornflower' => 'elegant'
			)
		);

		$flatted = RegistryHelper::flatten($array);

		$this->assertEquals($flatted['pos1.sunflower'], 'love');

		$flatted = RegistryHelper::flatten($array, '/');

		$this->assertEquals($flatted['pos1/sunflower'], 'love');
	}

	/**
	 * Data provider for object inputs
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToArray()
	{
		return array(
			'string' => array(
				'foo',
				false,
				array('foo')
			),
			'array' => array(
				array('foo'),
				false,
				array('foo')
			),
			'array_recursive' => array(
				array('foo' => array(
					(object) array('bar' => 'bar'),
					(object) array('baz' => 'baz')
				)),
				true,
				array('foo' => array(
					array('bar' => 'bar'),
					array('baz' => 'baz')
				))
			),
			'iterator' => array(
				array('foo' => new \ArrayIterator(array('bar' => 'baz'))),
				true,
				array('foo' => array('bar' => 'baz'))
			)
		);
	}

	/**
	 * testToArray
	 *
	 * @param $input
	 * @param $recursive
	 * @param $expect
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToArray
	 * @covers        Windwalker\Utilities\ArrayHelper::toArray
	 */
	public function testToArray($input, $recursive, $expect)
	{
		$this->assertEquals($expect, RegistryHelper::toArray($input, $recursive));
	}

	public function testDumpObjectValue()
	{
		$data = new StubDumpable(new StubDumpable);

		$dumped = RegistryHelper::dumpObjectValues($data);

		$this->assertEquals('foo', $dumped['foo']);
		$this->assertEquals('bar', $dumped['bar']);
		$this->assertNull($dumped['data']['self']);
		$this->assertEquals(RegistryHelper::dumpObjectValues(new StubDumpable), $dumped['data']['new']);
		$this->assertEquals(array('sakura', 'rose'), $dumped['data']['flower']);
		$this->assertEquals(array('wind' => 'walker'), $dumped['iterator']);
	}
}
