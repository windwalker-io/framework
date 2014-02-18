<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Data\Tests;

use Joomla\Data;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/buran.php';
require_once __DIR__ . '/Stubs/vostok.php';


/**
 * Tests for the Joomla\Data\DataSet class.
 *
 * @since  1.0
 */
class DataSetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var    \Joomla\Data\DataSet
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Tests the Joomla\Data\DataSet::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->assertEmpty(TestHelper::getValue(new Data\DataSet, 'objects'), 'New list should have no objects.');

		$input = array(
			'key' => new Data\DataObject(array('foo' => 'bar'))
		);
		$new = new Data\DataSet($input);

		$this->assertEquals($input, TestHelper::getValue($new, 'objects'), 'Check initialised object list.');
	}

	/**
	 * Tests the Joomla\Data\DataSet::__construct method with an array that does not contain Data objects.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\DataSet::__construct
	 * @expectedException  \InvalidArgumentException
	 * @since           1.0
	 */
	public function test__construct_array()
	{
		new Data\DataSet(array('foo'));
	}

	/**
	 * Tests the Joomla\Data\DataSet::__construct method with scalar input.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\DataSet::__construct
	 * @expectedException  \PHPUnit_Framework_Error
	 * @since           1.0
	 */
	public function test__construct_scalar()
	{
		new Data\DataSet('foo');
	}

	/**
	 * Tests the Joomla\Data\DataSet::__call method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__call
	 * @since   1.0
	 */
	public function test__call()
	{
		$this->assertThat(
			$this->instance->launch('go'),
			$this->equalTo(array(1 => 'go'))
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::__get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__get
	 * @since   1.0
	 */
	public function test__get()
	{
		$this->assertThat(
			$this->instance->pilot,
			$this->equalTo(array(0 => null, 1 => 'Yuri Gagarin'))
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::__isset method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__isset
	 * @since   1.0
	 */
	public function test__isset()
	{
		$this->assertTrue(isset($this->instance->pilot), 'Property exists.');

		$this->assertFalse(isset($this->instance->duration), 'Unknown property');
	}

	/**
	 * Tests the Joomla\Data\DataSet::__set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__set
	 * @since   1.0
	 */
	public function test__set()
	{
		$this->instance->successful = 'yes';

		$this->assertThat(
			$this->instance->successful,
			$this->equalTo(array(0 => 'yes', 1 => 'YES'))
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::__unset method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::__unset
	 * @since   1.0
	 */
	public function test__unset()
	{
		unset($this->instance->pilot);

		$this->assertNull($this->instance[1]->pilot);
	}

	/**
	 * Tests the Joomla\Data\DataSet::count method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::count
	 * @since   1.0
	 */
	public function testCount()
	{
		$this->assertCount(2, $this->instance);
	}

	/**
	 * Tests the Joomla\Data\DataSet::clear method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::clear
	 * @since   1.0
	 */
	public function testClear()
	{
		$this->assertGreaterThan(0, count($this->instance), 'Check there are objects set.');
		$this->instance->clear();
		$this->assertCount(0, $this->instance, 'Check the objects were cleared.');
	}

	/**
	 * Tests the Joomla\Data\DataSet::current method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::current
	 * @since   1.0
	 */
	public function testCurrent()
	{
		$object = $this->instance[0];

		$this->assertThat(
			$this->instance->current(),
			$this->equalTo($object)
		);

		$new = new Data\DataSet(array('foo' => new Data\DataObject));

		$this->assertThat(
			$new->current(),
			$this->equalTo(new Data\DataObject)
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::dump method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::dump
	 * @since   1.0
	 */
	public function testDump()
	{
		$this->assertEquals(
			array(
				new \stdClass,
				(object) array(
					'mission' => 'Vostok 1',
					'pilot' => 'Yuri Gagarin',
				),
			),
			$this->instance->dump()
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::jsonSerialize method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::jsonSerialize
	 * @since   1.0
	 */
	public function testJsonSerialize()
	{
		$this->assertEquals(
			array(
				new \stdClass,
				(object) array(
					'mission' => 'Vostok 1',
					'pilot' => 'Yuri Gagarin',
				),
			),
			$this->instance->jsonSerialize()
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::key method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::key
	 * @since   1.0
	 */
	public function testKey()
	{
		$this->assertEquals(0, $this->instance->key());
	}

	/**
	 * Tests the Joomla\Data\DataSet::keys method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::keys
	 * @since   1.0
	 */
	public function testKeys()
	{
		$instance = new Data\DataSet;
		$instance['key1'] = new Data\DataObject;
		$instance['key2'] = new Data\DataObject;

		$this->assertEquals(array('key1', 'key2'), $instance->keys());
	}

	/**
	 * Tests the Joomla\Data\DataSet::next method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::next
	 * @since   1.0
	 */
	public function testNext()
	{
		$this->instance->next();
		$this->assertThat(
			TestHelper::getValue($this->instance, 'current'),
			$this->equalTo(1)
		);

		$this->instance->next();
		$this->assertThat(
			TestHelper::getValue($this->instance, 'current'),
			$this->equalTo(false)
		);
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetExists method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::offsetExists
	 * @since   1.0
	 */
	public function testOffsetExists()
	{
		$this->assertTrue($this->instance->offsetExists(0));
		$this->assertFalse($this->instance->offsetExists(2));
		$this->assertFalse($this->instance->offsetExists('foo'));
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetGet method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::offsetGet
	 * @since   1.0
	 */
	public function testOffsetGet()
	{
		$this->assertInstanceOf('Joomla\Data\Tests\JDataBuran', $this->instance->offsetGet(0));
		$this->assertInstanceOf('Joomla\Data\Tests\JDataVostok', $this->instance->offsetGet(1));
		$this->assertNull($this->instance->offsetGet('foo'));
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetSet method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::OffsetSet
	 * @since   1.0
	 */
	public function testOffsetSet()
	{
		$this->instance->offsetSet(0, new Data\DataObject);
		$objects = TestHelper::getValue($this->instance, 'objects');

		$this->assertEquals(new Data\DataObject, $objects[0], 'Checks explicit use of offsetSet.');

		$this->instance[] = new Data\DataObject;
		$this->assertInstanceOf('Joomla\Data\DataObject', $this->instance[1], 'Checks the array push equivalent with [].');

		$this->instance['foo'] = new Data\DataObject;
		$this->assertInstanceOf('Joomla\Data\DataObject', $this->instance['foo'], 'Checks implicit usage of offsetSet.');
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetSet method for an expected exception
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\DataSet::OffsetSet
	 * @expectedException  InvalidArgumentException
	 * @since           1.0
	 */
	public function testOffsetSet_exception1()
	{
		// By implication, this will call offsetSet.
		$this->instance['foo'] = 'bar';
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetUnset method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::OffsetUnset
	 * @since   1.0
	 */
	public function testOffsetUnset()
	{
		$this->instance->offsetUnset(0);
		$objects = TestHelper::getValue($this->instance, 'objects');

		$this->assertFalse(isset($objects[0]));
	}

	/**
	 * Tests the Joomla\Data\DataSet::offsetRewind method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::rewind
	 * @since   1.0
	 */
	public function testOffsetRewind()
	{
		TestHelper::setValue($this->instance, 'current', 'foo');

		$this->instance->rewind();
		$this->assertEquals(0, $this->instance->key());

		$this->instance->clear();
		$this->assertFalse($this->instance->key());
	}

	/**
	 * Tests the Joomla\Data\DataSet::valid method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::valid
	 * @since   1.0
	 */
	public function testValid()
	{
		$this->assertTrue($this->instance->valid());

		TestHelper::setValue($this->instance, 'current', null);

		$this->assertFalse($this->instance->valid());
	}

	/**
	 * Test that Data\DataSet::_initialise method indirectly.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\DataSet::_initialise
	 * @since   1.0
	 */
	public function test_initialise()
	{
		$this->assertInstanceOf('Joomla\Data\Tests\JDataBuran', $this->instance[0]);
		$this->assertInstanceOf('Joomla\Data\Tests\JDataVostok', $this->instance[1]);
	}

	/*
	 * Ancillary tests.
	 */

	/**
	 * Tests using Data\DataSet in a foreach statement.
	 *
	 * @return  void
	 *
	 * @coversNothing  Integration test.
	 * @since          1.0
	 */
	public function test_foreach()
	{
		// Test multi-item list.
		$tests = array();

		foreach ($this->instance as $key => $object)
		{
			$tests[] = $object->mission;
		}

		$this->assertEquals(array(null, 'Vostok 1'), $tests);

		// Tests single item list.
		$this->instance->clear();
		$this->instance['1'] = new Data\DataObject;
		$runs = 0;

		foreach ($this->instance as $key => $object)
		{
			$runs++;
		}

		$this->assertEquals(1, $runs);

		// Exhaustively testing unsetting within a foreach.
		$this->instance['2'] = new Data\DataObject;
		$this->instance['3'] = new Data\DataObject;
		$this->instance['4'] = new Data\DataObject;
		$this->instance['5'] = new Data\DataObject;

		$runs = 0;

		foreach ($this->instance as $k => $v)
		{
			$runs++;

			if ($k != 3)
			{
				unset($this->instance[$k]);
			}
		}

		$this->assertFalse($this->instance->offsetExists(1), 'Index 1 should have been unset.');
		$this->assertFalse($this->instance->offsetExists(2), 'Index 2 should have been unset.');
		$this->assertTrue($this->instance->offsetExists(3), 'Index 3 should be set.');
		$this->assertFalse($this->instance->offsetExists(4), 'Index 4 should have been unset.');
		$this->assertFalse($this->instance->offsetExists(5), 'Index 5 should have been unset.');
		$this->assertCount(1, $this->instance);
		$this->assertEquals(5, $runs, 'Oops, the foreach ran too many times.');
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since  1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Data\DataSet(
			array(
				new JDataBuran,
				new JDataVostok(array('mission' => 'Vostok 1', 'pilot' => 'Yuri Gagarin')),
			)
		);
	}
}
