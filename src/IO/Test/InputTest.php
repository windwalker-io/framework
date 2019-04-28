<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO\Test;

use Windwalker\Filter\InputFilter;
use Windwalker\IO\Input;
use Windwalker\Test\TestHelper;
use Windwalker\IO\CookieInput;

/**
 * Test class of Input
 *
 * @since 2.0
 */
class InputTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp(): void
    {
        // $this->instance = new Input;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Test the Windwalker\IO\Input::__construct method.
     *
     * @return  void
     *
     * @throws \ReflectionException
     * @covers  \Windwalker\IO\Input::__construct
     */
    public function test__construct()
    {
        // Default constructor call
        $instance = new Input();

        $this->assertEquals(
            $_REQUEST,
            TestHelper::getValue($instance, 'data')
        );

        $this->assertInstanceOf(
            'Windwalker\Filter\InputFilter',
            TestHelper::getValue($instance, 'filter')
        );

        // Given source & filter
        $instance = new Input($_GET, new InputFilter());

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
     * @throws \ReflectionException
     */
    public function testPrepareSource()
    {
        $_REQUEST['foo'] = 'bar';

        $instance = new Input();

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
     * @throws \ReflectionException
     * @covers \Windwalker\IO\Input::__get
     */
    public function test__get()
    {
        $instance = $this->newInstance([]);

        self::assertEquals($_GET, $instance->get->getRawData());

        $inputs = TestHelper::getValue($instance, 'inputs');

        // Previously cached input
        $this->assertArrayHasKey('get', $inputs);

        $this->assertInstanceOf(Input::class, $inputs['get']);

        $this->assertEquals($_GET, $instance->get->getRawData());

        $cookies = $instance->cookie;
        $this->assertInstanceOf(Input::class, $cookies);
        $this->assertInstanceOf(CookieInput::class, $cookies);

        // If nothing is returned
        $this->assertEquals(null, $instance->foobar);
    }

    /**
     * Method to test count().
     *
     * @return void
     *
     * @covers \Windwalker\IO\Input::count
     */
    public function testCount()
    {
        $input = $this->newInstance(['foo' => 2, 'bar' => 3, 'gamma' => 4]);

        $this->assertEquals(3, $input->count());

        $input = $this->newInstance();

        $this->assertEquals(0, $input->count());
    }

    /**
     * Test the Windwalker\IO\Input::get method with a normal value.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWithStandardValue()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        $this->assertEquals('bar', $instance->get('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::get method with empty string.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWithEmptyString()
    {
        $instance = $this->newInstance(['foo' => '']);

        $this->assertEquals('', $instance->get('foo'));

        $this->assertIsString($instance->get('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::get method with integer 0.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWith0()
    {
        $instance = $this->newInstance(['foo' => 0]);

        $this->assertEquals(0, $instance->getInt('foo'));

        $this->assertIsInt($instance->getInt('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::get method with float 0.0.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWith0Point0()
    {
        $instance = $this->newInstance(['foo' => 0.0]);

        $this->assertEquals(0.0, $instance->getFloat('foo'));

        $this->assertIsFloat($instance->getFloat('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::get method with string "0".
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWithString0()
    {
        $instance = $this->newInstance(['foo' => "0"]);

        $this->assertEquals("0", $instance->get('foo'));

        $this->assertIsString($instance->get('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::get method with false.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetWithFalse()
    {
        $instance = $this->newInstance(['foo' => false]);

        $this->assertFalse($instance->getBoolean('foo'));

        $this->assertIsBool($instance->getBool('foo'));
    }

    /**
     * Tests retrieving a default value..
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::get
     * @since   2.0
     */
    public function testGetDefault()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        // Test the get method.
        $this->assertEquals('default', $instance->get('default_value', 'default'));
    }

    /**
     * Test the Windwalker\IO\Input::def method.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::def
     * @since   2.0
     */
    public function testDefNotReadWhenValueExists()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        $instance->def('foo', 'nope');

        $this->assertEquals('bar', $instance->get('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::def method.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::def
     * @since   2.0
     */
    public function testDefRead()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        $instance->def('bar', 'nope');

        $this->assertEquals('nope', $instance->get('bar'));
    }

    /**
     * Test the Windwalker\IO\Input::set method.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::set
     * @since   2.0
     */
    public function testSet()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        $instance->set('foo', 'gamma');

        $this->assertEquals('gamma', $instance->get('foo'));
    }

    /**
     * Test the Windwalker\IO\Input::exists method.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::exists
     * @since   3.2
     */
    public function testExists()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

        $this->assertTrue($instance->exists('foo'));

        $this->assertFalse($instance->exists('bar'));
    }

    /**
     * testGetByPath
     *
     * @covers  \Windwalker\IO\Input::getByPath
     *
     * @return  void
     */
    public function testGetByPath()
    {
        $array = [
            'var2' => 34,
            'var3' => ['var2' => 'test123'],
            'var4' => ['var1' => ['var2' => 'test']],
        ];

        $input = $this->newInstance($array);

        $this->assertEquals('test', $input->get('var4.var1.var2', '.'));
        $this->assertEquals('default', $input->get('var2.foo.bar', 'default', '.'));
        $this->assertEquals('123', $input->get('var3.var2', null, InputFilter::INTEGER, '.'));
    }

    /**
     * testGetByPath
     *
     * @covers  \Windwalker\IO\Input::setByPath
     *
     * @return  void
     */
    public function testSetByPath()
    {
        $array = [
            'var2' => 34,
            'var3' => ['var2' => 'test123'],
            'var4' => ['var1' => ['var2' => 'test']],
        ];

        $input = $this->newInstance($array);

        $input->set('var3.var2', '2567-flower', '.');

        $this->assertEquals('2567', $input->get('var3.var2', null, InputFilter::INTEGER, '.'));
    }

    /**
     * Test the Windwalker\IO\Input::getArray method without specified variables.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::compact
     * @since   2.0
     */
    public function testToArray()
    {
        $array = [
            'var2' => 34,
            'var3' => ['var2' => 'test'],
            'var4' => ['var1' => ['var2' => 'test']],
            'var5' => ['foo' => []],
            'var6' => ['bar' => null],
            'var7' => null,
        ];

        $input = $this->newInstance($array);

        $this->assertEquals($array, $input->toArray());
    }

    public function testGetArray()
    {
        $array = [
            'var2' => 34,
            'var3' => ['var2' => 'test foo/bar'],
            'var4' => ['var1' => ['var2' => 'test']],
            'var5' => ['foo' => []],
            'var6' => ['bar' => null],
            'var7' => null,
        ];

        $input = $this->newInstance($array);

        $this->assertEquals(['var2' => 'test foo/bar'], $input->getArray('var3'));
        $this->assertEquals(['var2' => 'testfoobar'], $input->getArray('var3', null, '.', 'cmd'));
    }

    /**
     * Test the Windwalker\IO\Input::compact method.
     *
     * @return  void
     *
     * @covers  \Windwalker\IO\Input::compact
     * @since   2.0
     */
    public function testCompact()
    {
        $array = [
            'var1' => 'value1',
            'var2' => 34,
            'var3' => ['test'],
        ];

        $input = $this->newInstance($array);

        $this->assertEquals(
            $array,
            $input->compact(
                ['var1' => 'raw', 'var2' => 'raw', 'var3' => 'raw']
            )
        );
    }

    /**
     * Method to test __call().
     *
     * @return void
     *
     * @covers \Windwalker\IO\Input::__call
     */
    public function test__call()
    {
        $instance = $this->newInstance(['foo' => 'bar']);

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
     * @covers \Windwalker\IO\Input::getMethod
     */
    public function testGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'custom';

        $instance = $this->newInstance([]);

        $this->assertEquals('CUSTOM', $instance->getMethod());
    }

    /**
     * Method to test serialize().
     *
     * @return void
     *
     * @covers \Windwalker\IO\Input::serialize
     */
    public function testSerialize()
    {
        $_SERVER['REQUEST_METHOD'] = 'custom';

        $instance = $this->newInstance(['foo' => 'bar123']);

        $input = unserialize(serialize($instance));

        $this->assertEquals('bar123', $input->get('foo'));
        $this->assertEquals('123', $input->getInt('foo'));
    }

    /**
     * Method to test unserialize().
     *
     * @return void
     *
     * @covers \Windwalker\IO\Input::unserialize
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
     * @throws \ReflectionException
     * @covers \Windwalker\IO\Input::loadAllInputs
     */
    public function testLoadAllInputs()
    {
        // Remove the following lines when you implement this test.
        $this->markTestSkipped(
            'A bug that the static $loaded variable has been set to true.....'
        );

        $instance = $this->newInstance([]);
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
    protected function newInstance($data = [])
    {
        return new Input($data);
    }
}
