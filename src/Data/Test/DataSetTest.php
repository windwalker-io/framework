<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Data\Test;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;

/**
 * Test class of DataSet
 *
 * @since 2.0
 */
class DataSetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var DataSet
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new DataSet($this->getTestData());
	}

	/**
	 * getTestData
	 *
	 * @return  Data[]
	 */
	protected function getTestData()
	{
		return array(
			new Data(array('flower' => 'sakura')),
			new Data(array('wind' => 'walker'))
		);
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
	 * Method to test bind().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::bind
	 */
	public function testBind()
	{
		$data = $this->getTestData();

		$dataset = new DataSet;

		$dataset->bind($data);

		$this->assertSame($data[0], $dataset[0]);
		$this->assertSame($data[1], $dataset[1]);

		$dataset = new DataSet;

		// Bind iterator
		$dataset->bind($this->instance);

		$this->assertEquals($data[0], $this->instance[0]);
		$this->assertEquals($data[1], $this->instance[1]);
	}

	/**
	 * Method to test __get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::__get
	 */
	public function test__getAnd__set()
	{
		// Batch set to every data.
		$this->instance->olive = 'peace';

		foreach ($this->instance as $data)
		{
			$this->assertEquals('peace', $data->olive);
		}

		$this->instance[0]->foo = 'zero';
		$this->instance[1]->foo = 'one';

		$this->assertEquals(array('zero', 'one'), $this->instance->foo);

		// Get empty
		$this->assertEquals(array('sakura', null), $this->instance->flower);
	}

	/**
	 * Method to test __isset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::__isset
	 */
	public function test__isset()
	{
		// If only one data has this field, we return true.
		$this->assertTrue(isset($this->instance->wind));

		// All data dose not have this field, return false.
		$this->assertFalse(isset($this->instance->fire));
	}

	/**
	 * Method to test __unset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::__unset
	 */
	public function test__unset()
	{
		$dataset = new DataSet($this->getTestData());

		// Batch set to every data.
		$dataset->ninja = 'darts';

		$this->assertEquals(array('darts', 'darts'), $dataset->ninja);

		unset($dataset->ninja);

		$this->assertEquals(array(null, null), $dataset->ninja);
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance[1]));
		$this->assertFalse(isset($this->instance[4]));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::offsetGet
	 * @covers Windwalker\Data\DataSet::offsetSet
	 */
	public function testOffsetGetAndSet()
	{
		$data = $this->getTestData();

		$this->assertEquals($data[0], $this->instance[0]);

		$this->instance[3] = new Data(array('foo' => 'bar'));

		$this->assertEquals('bar', $this->instance[3]->foo);

		$this->instance[4] = new \stdClass;

		$this->assertInstanceOf('Windwalker\Data\Data', $this->instance[4]);
	}

	/**
	 * Method to test clear().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::clear
	 */
	public function testClear()
	{
		$dataset = clone $this->instance;

		$dataset->clear();

		$this->assertEquals(0, count($dataset));
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::offsetUnset
	 */
	public function testOffsetUnset()
	{
		unset($this->instance[0]);

		$this->assertEquals(1, count($this->instance));
		$this->assertFalse(isset($this->instance[0]));
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::getIterator
	 */
	public function testGetIterator()
	{
		$array = iterator_to_array($this->instance);

		$this->assertEquals('sakura', $array[0]->flower);
	}

	/**
	 * Method to test serialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::serialize
	 */
	public function testSerialize()
	{
		// Batch set to every data.
		$this->instance->ninja = 'darts';

		$dataset = unserialize(serialize($this->instance));

		$this->assertEquals(array('darts', 'darts'), $dataset->ninja);
	}

	/**
	 * Method to test count().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::count
	 */
	public function testCount()
	{
		$this->assertEquals(2, count($this->instance));
	}

	/**
	 * Method to test jsonSerialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::jsonSerialize
	 */
	public function testJsonSerialize()
	{
		if (version_compare(PHP_VERSION, '5.4', '>'))
		{
			$json = json_encode($this->instance);
		}
		else
		{
			$json = json_encode((array) $this->instance->getIterator());
		}

		$this->assertJsonStringEqualsJsonString('[{"flower":"sakura"},{"wind":"walker"}]', $json);
	}

	/**
	 * Method to test isNull().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\DataSet::isNull
	 */
	public function testIsNull()
	{
		$this->assertFalse($this->instance->isNull());

		$dataset = new DataSet;

		$this->assertTrue($dataset->isNull());
	}

	/**
	 * testDump
	 *
	 * @return  void
	 */
	public function testDump()
	{
		$this->assertEquals($this->getTestData(), $this->instance->dump());
	}

	/**
	 * Method to test map()
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::map
	 */
	public function testMap()
	{
		$this->instance->map(function($data)
		{
			$data->foo = 'bar';

			return $data;
		});

		$this->assertEquals(array('bar', 'bar'), $this->instance->foo);
	}

	/**
	 * Method to test walk()
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::walk
	 */
	public function testWalk()
	{
		$this->instance->walk(function(&$data, $key, $userdata)
		{
			$data->foo = $userdata . ':' . $key;
		}, 'prefix');

		$this->assertEquals(array('prefix:0', 'prefix:1'), $this->instance->foo);
	}

	/**
	 * testClone
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::__clone
	 */
	public function testClone()
	{
		$expected = new DataSet($this->getTestData());

		$dataset = clone $expected;

		$this->assertNotSame($expected, $dataset);
		$this->assertNotSame($expected[0], $dataset[0]);
		$this->assertNotSame($expected[1], $dataset[1]);
	}

	/**
	 * testGetKeys
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::getKeys
	 */
	public function testGetKeys()
	{
		$dataset = new DataSet($this->getTestData());

		$this->assertEquals(array(0, 1), $dataset->getKeys());

		$dataset['flower'] = new Data;

		$this->assertEquals(array(0, 1, 'flower'), $dataset->getKeys());
	}

	/**
	 * testKsort
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::ksort
	 */
	public function testKsort()
	{
		$dataset = new DataSet;
		$dataset[1] = array('flower' => 'sakura');
		$dataset[2] = array('flower' => 'rose');
		$dataset[0] = array('flower' => 'sunflower');

		$dataset->ksort();

		$this->assertEquals(array('sunflower', 'sakura', 'rose'), array_values($dataset->flower));

		$dataset = new DataSet;
		$dataset['001'] = array('flower' => 'sakura');
		$dataset['2'] = array('flower' => 'rose');
		$dataset['00030'] = array('flower' => 'sunflower');

		$dataset->ksort(SORT_STRING);

		$this->assertEquals(array('sunflower', 'sakura', 'rose'), array_values($dataset->flower));
	}

	/**
	 * testKrsort
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::krsort
	 */
	public function testKrsort()
	{
		$dataset = new DataSet;
		$dataset[1] = array('flower' => 'sakura');
		$dataset[2] = array('flower' => 'rose');
		$dataset[0] = array('flower' => 'sunflower');

		$dataset->krsort();

		$this->assertEquals(array('rose', 'sakura', 'sunflower'), array_values($dataset->flower));

		$dataset = new DataSet;
		$dataset['001'] = array('flower' => 'sakura');
		$dataset['2'] = array('flower' => 'rose');
		$dataset['00030'] = array('flower' => 'sunflower');

		$dataset->krsort(SORT_STRING);

		$this->assertEquals(array('rose', 'sakura', 'sunflower'), array_values($dataset->flower));
	}

	/**
	 * testUksort
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::uksort
	 */
	public function testUksort()
	{
		$this->markTestSkipped();
	}

	/**
	 * testShuffle
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Data\DataSet::shuffle
	 */
	public function testShuffle()
	{
		$this->markTestSkipped();
	}
}
