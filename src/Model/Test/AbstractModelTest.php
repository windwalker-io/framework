<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Model\Test;

use Windwalker\Model\Test\Stub\StubModel;
use Windwalker\Registry\Registry;

/**
 * Test class of AbstractModel
 *
 * @since 2.0
 */
class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubModel
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
		$array = array(
			'abc' => 123,
			's' => array(
				'g' => 321
			)
		);

		$this->instance = new StubModel(new Registry($array));
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
	 * Method to test getState().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::getState
	 */
	public function testGetState()
	{
		$this->assertInstanceOf('Windwalker\Registry\Registry', $this->instance->getState());
	}

	/**
	 * Method to test setState().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::setState
	 */
	public function testSetState()
	{
		$state = new Registry;

		$this->instance->setState($state);

		$this->assertSame($state, $this->instance->getState());
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::get
	 */
	public function testGet()
	{
		$state = $this->instance->getState();

		$state['foo.bar'] = 'flower';

		$this->assertEquals('flower', $this->instance->get('foo.bar'));
		$this->assertEquals('def', $this->instance->get('foo.bar2', 'def'));
		$this->assertNull($this->instance->get('foo.bar3'));
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::set
	 */
	public function testSet()
	{
		$this->instance->set('flower.olive', 'peace');

		$state = $this->instance->getState();

		$state['foo.bar'] = 'flower';

		$this->assertEquals('peace', $this->instance->get('flower.olive'));
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance['abc']));
		$this->assertFalse(isset($this->instance['cba']));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::offsetGet
	 */
	public function testOffsetGet()
	{
		$this->assertEquals(123, $this->instance['abc']);
		$this->assertEquals(321, $this->instance['s.g']);
	}

	/**
	 * Method to test offsetSet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::offsetSet
	 */
	public function testOffsetSet()
	{
		$this->instance['a.b'] = 567;

		$this->assertEquals(567, $this->instance['a.b']);
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Model\AbstractModel::offsetUnset
	 */
	public function testOffsetUnset()
	{
		unset($this->instance['s.g']);

		$this->assertNull($this->instance['s.g']);
	}
}
