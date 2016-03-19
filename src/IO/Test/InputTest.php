<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Test;

use Windwalker\Filter\InputFilter;
use Windwalker\IO\Input;
use Windwalker\Test\TestHelper;

/**
 * Test class of Input
 *
 * @since 2.0
 */
class InputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Input
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
		// $this->instance = new Input;
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
	 * Test the Windwalker\Input\Input::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::__construct
	 */
	public function test__construct()
	{
		// Default constructor call
		$instance = new Input;

		$this->assertEquals(
			$_REQUEST,
			TestHelper::getValue($instance, 'data')
		);

		$this->assertInstanceOf(
			'Windwalker\Filter\InputFilter',
			TestHelper::getValue($instance, 'filter')
		);

		// Given source & filter
		$instance = new Input($_GET, new InputFilter);

		$this->assertEquals(
			$_GET,
			TestHelper::getValue($instance, 'data')
		);

		$this->assertInstanceOf(
			'Windwalker\Filter\InputFilter',
			TestHelper::getValue($instance, 'filter')
		);
	}

	/**
	 * testPrepareSource
	 *
	 * @return  void
	 */
	public function testPrepareSource()
	{
		$_REQUEST['foo'] = 'bar';

		$instance = new Input;

		$instance->prepareSource($_REQUEST, true);

		$this->assertSame($_REQUEST, TestHelper::getValue($instance, 'data'));

		$instance->set('foo', 'baz');

		$this->assertEquals('baz', $instance->get('foo'));
	}

	/**
	 * Method to test __get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::__get
	 */
	public function test__get()
	{
		$instance = $this->newInstance(array());

		$this->assertAttributeEquals($_GET, 'data', $instance->get);

		$inputs = TestHelper::getValue($instance, 'inputs');

		// Previously cached input
		$this->assertArrayHasKey('get', $inputs);

		$this->assertTrue($inputs['get'] instanceof Input);

		$this->assertAttributeEquals($_GET, 'data', $instance->get);

		$cookies = $instance->cookie;
		$this->assertInstanceOf('Windwalker\IO\Input', $cookies);
		$this->assertInstanceOf('Windwalker\IO\Cookie', $cookies);

		// If nothing is returned
		$this->assertEquals(null, $instance->foobar);
	}

	/**
	 * Method to test count().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::count
	 */
	public function testCount()
	{
		$input = $this->newInstance(array('foo' => 2, 'bar' => 3, 'gamma' => 4));

		$this->assertEquals(3, $input->count());

		$input = $this->newInstance();

		$this->assertEquals(0, $input->count());
	}


	/**
	 * Test the Windwalker\Input\Input::get method with a normal value.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWithStandardValue()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$this->assertEquals('bar', $instance->get('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::get method with empty string.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWithEmptyString()
	{
		$instance = $this->newInstance(array('foo' => ''));

		$this->assertEquals('', $instance->get('foo'));

		$this->assertInternalType('string', $instance->get('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::get method with integer 0.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWith0()
	{
		$instance = $this->newInstance(array('foo' => 0));

		$this->assertEquals(0, $instance->getInt('foo'));

		$this->assertInternalType('integer', $instance->getInt('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::get method with float 0.0.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWith0Point0()
	{
		$instance = $this->newInstance(array('foo' => 0.0));

		$this->assertEquals(0.0, $instance->getFloat('foo'));

		$this->assertInternalType('float', $instance->getFloat('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::get method with string "0".
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWithString0()
	{
		$instance = $this->newInstance(array('foo' => "0"));

		$this->assertEquals("0", $instance->get('foo'));

		$this->assertInternalType('string', $instance->get('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::get method with false.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetWithFalse()
	{
		$instance = $this->newInstance(array('foo' => false));

		$this->assertFalse($instance->getBoolean('foo'));

		$this->assertInternalType('boolean', $instance->getBool('foo'));
	}

	/**
	 * Tests retrieving a default value..
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetDefault()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		// Test the get method.
		$this->assertEquals('default', $instance->get('default_value', 'default'));
	}

	/**
	 * Test the Windwalker\Input\Input::def method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::def
	 * @since   2.0
	 */
	public function testDefNotReadWhenValueExists()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$instance->def('foo', 'nope');

		$this->assertEquals('bar', $instance->get('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::def method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::def
	 * @since   2.0
	 */
	public function testDefRead()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$instance->def('bar', 'nope');

		$this->assertEquals('nope', $instance->get('bar'));
	}

	/**
	 * Test the Windwalker\Input\Input::set method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::set
	 * @since   2.0
	 */
	public function testSet()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$instance->set('foo', 'gamma');

		$this->assertEquals('gamma', $instance->get('foo'));
	}

	/**
	 * Test the Windwalker\Input\Input::exists method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::exists
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExists()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$this->assertTrue($instance->exists('foo'));

		$this->assertFalse($instance->exists('bar'));
	}

	/**
	 * Test the Windwalker\Input\Input::getArray method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetArray()
	{
		$array = array(
			'var1' => 'value1',
			'var2' => 34,
			'var3' => array('test')
		);

		$input = $this->newInstance($array);

		$this->assertEquals(
			$array,
			$input->getArray(
				array('var1' => 'raw', 'var2' => 'raw', 'var3' => 'raw')
			)
		);

		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test the Windwalker\Input\Input::get method using a nested data set.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::get
	 * @since   2.0
	 */
	public function testGetArrayNested()
	{
		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test'),
			'var4' => array('var1' => array('var2' => 'test'))
		);

		$input = $this->newInstance($array);

		$this->assertEquals(
			array('var4' => array('var1' => array('var2' => 'test'))),
			$input->getArray(
				array(
					'var4' => array(
						'var1' => array('var2' => 'test')
					)
				)
			)
		);
	}

	/**
	 * testGetByPath
	 *
	 * @covers  Windwalker\Input\Input::getByPath
	 *
	 * @return  void
	 */
	public function testGetByPath()
	{
		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test123'),
			'var4' => array('var1' => array('var2' => 'test'))
		);

		$input = $this->newInstance($array);

		$this->assertEquals('test', $input->getByPath('var4.var1.var2'));
		$this->assertEquals('default', $input->getByPath('var2.foo.bar', 'default'));
		$this->assertEquals('123', $input->getByPath('var3.var2', null, InputFilter::INTEGER));
	}

	/**
	 * testGetByPath
	 *
	 * @covers  Windwalker\Input\Input::setByPath
	 *
	 * @return  void
	 */
	public function testSetByPath()
	{
		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test123'),
			'var4' => array('var1' => array('var2' => 'test'))
		);

		$input = $this->newInstance($array);

		$input->setByPath('var3.var2', '2567-flower');

		$this->assertEquals('2567', $input->getByPath('var3.var2', null, InputFilter::INTEGER));
	}

	/**
	 * Test the Windwalker\Input\Input::getArray method without specified variables.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Input::getArray
	 * @since   2.0
	 */
	public function testGetArrayWithoutSpecifiedVariables()
	{
		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test'),
			'var4' => array('var1' => array('var2' => 'test')),
			'var5' => array('foo' => array()),
			'var6' => array('bar' => null),
			'var7' => null
		);

		$input = $this->newInstance($array);

		$this->assertEquals($input->getArray(), $array);
	}

	/**
	 * Method to test __call().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::__call
	 */
	public function test__call()
	{
		$instance = $this->newInstance(array('foo' => 'bar'));

		$this->assertEquals(
			'bar',
			$instance->getRaw('foo')
		);

		$this->assertEquals(
			'two',
			$instance->getRaw('one', 'two')
		);

		$this->assertNull(
			$instance->setRaw('one', 'two')
		);
	}

	/**
	 * Method to test getMethod().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::getMethod
	 */
	public function testGetMethod()
	{
		$_SERVER['REQUEST_METHOD'] = 'custom';

		$instance = $this->newInstance(array());

		$this->assertEquals('CUSTOM', $instance->getMethod());
	}

	/**
	 * Method to test serialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::serialize
	 */
	public function testSerialize()
	{
		$_SERVER['REQUEST_METHOD'] = 'custom';

		$instance = $this->newInstance(array('foo' => 'bar123'));

		$input = unserialize(serialize($instance));

		$this->assertEquals('bar123', $input->get('foo'));
		$this->assertEquals('123', $input->getInt('foo'));
	}

	/**
	 * Method to test unserialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::unserialize
	 * @TODO   Implement testUnserialize().
	 */
	public function testUnserialize()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadAllInputs().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Input::loadAllInputs
	 */
	public function testLoadAllInputs()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped(
			'A bug that the static $loaded variable has been set to true.....'
		);

		$instance = $this->newInstance(array());
		TestHelper::setValue($instance, 'loaded', false);

		$inputs = TestHelper::getValue($instance, 'inputs');
		$this->assertCount(0, $inputs);

		TestHelper::invoke($instance, 'loadAllInputs');

		$inputs = TestHelper::getValue($instance, 'inputs');
		$this->assertGreaterThan(0, count($inputs));
	}

	/**
	 * newInstance
	 *
	 * @param array $data
	 *
	 * @return  Input
	 */
	protected function newInstance($data = array())
	{
		return new Input($data);
	}
}
