<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Data\Test;

use Windwalker\Data\Data;
use Windwalker\Data\Test\Stub\FakeData;

/**
 * Test class of Data
 *
 * @since 2.0
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Data
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
		$this->instance = new Data($this->getTestData());
	}

	/**
	 * getTestData
	 *
	 * @return  array
	 */
	protected function getTestData()
	{
		return array(
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
	}

	/**
	 * Method to test bind().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::bind
	 */
	public function testBind()
	{
		$data = new Data;

		$data->bind($this->getTestData());

		$this->assertEquals('sakura', $data->flower);

		// $data->bind((array) new FakeData);
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::set
	 */
	public function testGetAndSet()
	{
		$this->instance->set('wind', 'walker');

		$this->assertEquals('walker', $this->instance->get('wind'));
		$this->assertEquals('talker', $this->instance->get('fire', 'talker'));
	}

	/**
	 * testExists
	 *
	 * @return  void
	 */
	public function testExists()
	{
		$this->assertTrue($this->instance->exists('flower'));
		$this->assertFalse($this->instance->exists('fire'));
	}

	/**
	 * Method to test __set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::__set
	 */
	public function test__getAnd__set()
	{
		$this->instance->wind = 'walker';

		$this->assertEquals('walker', $this->instance->wind);
		$this->assertEquals(null, $this->instance->fire);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::getIterator
	 */
	public function testGetIterator()
	{
		foreach ($this->instance as $name => $data)
		{
			$this->assertEquals('flower', $name);
			$this->assertEquals('sakura', $data);

			break;
		}

		$array = iterator_to_array($this->instance);

		$this->assertEquals('sakura', $array['flower']);
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance['flower']));
		$this->assertFalse(isset($this->instance['fire']));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::offsetGet
	 */
	public function testOffsetGetAndSet()
	{
		$this->instance['wind'] = 'walker';

		$this->assertEquals('sakura', $this->instance['flower']);
		$this->assertEquals('walker', $this->instance['wind']);

		try
		{
			$this->instance[null] = 'sunflower';
		}
		catch (\Exception $e)
		{
			$this->assertTrue($e instanceof \InvalidArgumentException);
		}
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::offsetUnset
	 */
	public function testOffsetUnset()
	{
		unset($this->instance->olive);

		$this->assertEquals(null, $this->instance->get('olive'));
	}

	/**
	 * Method to test count().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::count
	 */
	public function testCount()
	{
		$this->assertEquals(count($this->getTestData()), count($this->instance));
	}

	/**
	 * Method to test isNull().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Data\Data::isNull
	 */
	public function testIsNull()
	{
		$this->assertFalse($this->instance->isNull());

		$data = new Data;

		$this->assertTrue($data->isNull());
	}

	/**
	 * testDump
	 *
	 * @return  void
	 */
	public function testDump()
	{
		$compare = $this->getTestData();

		$this->assertEquals($compare, $this->instance->dump());
	}

	/**
	 * Method to test map()
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Data\Data::map
	 */
	public function testMap()
	{
		$data = new Data;

		$data->foo = 'bar';
		$data->baz = 'yoo';

		$data->map(function($value)
		{
			return strtoupper($value);
		});

		$this->assertEquals('YOO', $data->baz);
	}

	/**
	 * Method to test walk()
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Data\Data::walk
	 */
	public function testWalk()
	{
		$data = new Data;

		$data->foo = 'bar';
		$data->baz = 'yoo';

		$data->walk(function(&$value, $key, $userdata)
		{
			$value = $userdata . ':' . $key . ':' . strtoupper($value);
		}, 'prefix');

		$this->assertEquals('prefix:baz:YOO', $data->baz);
	}

	/**
	 * testClone
	 *
	 * @return  void
	 */
	public function testClone()
	{
		$data = new Data;
		$data->foo = new \stdClass;
		$data->bar = new \stdClass;
		$data->baz = 'yoo';

		$data2 = clone $data;

		$this->assertNotSame($data->foo, $data2->foo);
		$this->assertNotSame($data->foo, $data2->foo);
		$this->assertEquals('yoo', $data2->baz);
	}
}
