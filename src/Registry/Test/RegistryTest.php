<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Registry\Test;

use Windwalker\Registry\RegistryHelper;
use Windwalker\Registry\Registry;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of Registry
 *
 * @since 2.0
 */
class RegistryTest extends AbstractBaseTestCase
{
	/**
	 * Test instance.
	 *
	 * @var Registry
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
		$this->instance = new Registry($this->getTestData());
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
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test __clone().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::__clone
	 */
	public function test__clone()
	{
		$registry1 = new Registry($this->getTestData());

		$registry2 = clone $registry1;

		$this->assertEquals($registry1, $registry2);
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::__toString
	 */
	public function test__toString()
	{
		$this->assertJsonStringEqualsJsonString(json_encode($this->getTestData()), (string) $this->instance);
	}

	/**
	 * Method to test jsonSerialize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::jsonSerialize
	 */
	public function testJsonSerialize()
	{
		$this->assertJsonStringEqualsJsonString(json_encode($this->getTestData()), (string) $this->instance);
	}

	/**
	 * Method to test def().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::def
	 */
	public function testDef()
	{
		$this->assertNull($this->instance->get('lily'));

		$this->instance->def('lily', 'love');

		$this->assertEquals('love', $this->instance->get('lily'));
	}

	/**
	 * Method to test exists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::exists
	 */
	public function testExists()
	{
		$this->assertFalse($this->instance->exists('rose'));
		$this->assertTrue($this->instance->exists('flower'));
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::get
	 */
	public function testGet()
	{
		$this->assertEquals($this->instance->get('flower', 'canna'), 'sakura');

		$this->assertEquals($this->instance->get('not.exists', 'canna'), 'canna');

		$this->assertNull($this->instance->get('not.exists'));

		$this->assertEquals($this->instance->get('pos1.sunflower'), 'love');
	}

	/**
	 * Method to test loadArray().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::load
	 */
	public function testLoadArray()
	{
		$registry = new Registry;

		$registry->load($this->getTestData());

		$this->assertEquals($registry->get('olive'), 'peace');

		$this->assertEquals($registry->get('pos1.sunflower'), 'love');
	}

	/**
	 * Method to test loadObject().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::load
	 */
	public function testLoadObject()
	{
		$registry = new Registry;

		$registry->load((object) $this->getTestData());

		$this->assertEquals($registry->get('olive'), 'peace');

		$this->assertEquals($registry->get('pos1.sunflower'), 'love');
	}

	/**
	 * Method to test loadFile().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::loadFile
	 */
	public function testLoadFile()
	{
		$registry = new Registry;

		$this->assertEquals($registry->reset()->loadFile(__DIR__ . '/Stubs/flower.json', 'json')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadFile(__DIR__ . '/Stubs/flower.yml', 'yaml')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadFile(__DIR__ . '/Stubs/flower.ini', 'ini')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadFile(__DIR__ . '/Stubs/flower.xml', 'xml')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadFile(__DIR__ . '/Stubs/flower.php', 'php')->get('flower'), 'sakura');
	}

	/**
	 * Method to test loadString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::loadString
	 */
	public function testLoadString()
	{
		$registry = new Registry;

		$this->assertEquals($registry->reset()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.json'), 'json')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.yml'), 'yaml')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.ini'), 'ini')->get('flower'), 'sakura');
		$this->assertEquals($registry->reset()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.xml'), 'xml')->get('flower'), 'sakura');
	}

	/**
	 * Method to test merge().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::merge
	 */
	public function testMerge()
	{
		// Test recursive merge
		$object1 = '{
			"foo" : "foo value",
			"bar" : {
				"bar1" : "bar value 1",
				"bar2" : "bar value 2",
				"bar3" : "bar value 3"
			}
		}';
		$object2 = '{
			"foo" : "foo value",
			"bar" : {
				"bar2" : "new bar value 2",
				"bar3" : null
			}
		}';

		$registry1 = new Registry(json_decode($object1));
		$registry2 = new Registry(json_decode($object2));

		$registry1->merge($registry2);

		$this->assertEquals('new bar value 2', $registry1->get('bar.bar2'), 'Line: ' . __LINE__ . '. bar.bar2 should be override.');
		$this->assertEquals('bar value 1', $registry1->get('bar.bar1'), 'Line: ' . __LINE__ . '. bar.bar1 should not be override.');
		$this->assertSame('bar value 3', $registry1->get('bar.bar3'), 'Line: ' . __LINE__ . '. bar.bar3 should not be override.');

		$registry = new Registry(array('flower' => 'rose', 'honor' => 'Osmanthus month'));

		$registry->merge($this->instance);

		$this->assertEquals($registry->get('flower'), 'sakura');
		$this->assertEquals($registry->get('honor'), 'Osmanthus month');
	}

	/**
	 * Method to test merge().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::merge
	 */
	public function testMergeWithIgnoreValues()
	{
		// Test recursive merge
		$object1 = '{
			"foo" : "foo value",
			"bar" : {
				"bar1" : "bar value 1",
				"bar2" : "bar value 2",
				"bar3" : "bar value 3"
			}
		}';
		$object2 = '{
			"foo" : "foo value",
			"bar" : {
				"bar2" : "new bar value 2",
				"bar3" : ""
			}
		}';

		$registry1 = new Registry(json_decode($object1));
		$registry2 = new Registry(json_decode($object2));

		$registry1->setIgnoreValues(array(null, ''));
		$registry1->merge($registry2);

		$this->assertEquals('new bar value 2', $registry1->get('bar.bar2'), 'Line: ' . __LINE__ . '. bar.bar2 should be override.');
		$this->assertEquals('bar value 1', $registry1->get('bar.bar1'), 'Line: ' . __LINE__ . '. bar.bar1 should not be override.');
		$this->assertSame('bar value 3', $registry1->get('bar.bar3'), 'Line: ' . __LINE__ . '. bar.bar3 should not be override.');

		$registry = new Registry(array('flower' => 'rose', 'honor' => 'Osmanthus month'));

		$registry->merge($this->instance);

		$this->assertEquals($registry->get('flower'), 'sakura');
		$this->assertEquals($registry->get('honor'), 'Osmanthus month');
	}

	/**
	 * testMergeTo
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::mergeTo
	 */
	public function testMergeTo()
	{
		$registry = new Registry(array('sunflower' => 'shine', 'honor' => 'Osmanthus month'));

		$this->instance->mergeTo('pos1', $registry);

		$this->assertEquals($this->instance->get('pos1.sunflower'), 'shine');
		$this->assertEquals($this->instance->get('pos1.honor'), 'Osmanthus month');

		$this->instance->mergeTo('foo.bar', $registry);

		$this->assertEquals($this->instance->get('foo.bar.sunflower'), 'shine');
		$this->assertEquals($this->instance->get('foo.bar.honor'), 'Osmanthus month');
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance['flower']));
		$this->assertFalse(isset($this->instance['carbon']));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::offsetGet
	 */
	public function testOffsetGet()
	{
		$this->assertEquals($this->instance['flower'], 'sakura');
	}

	/**
	 * Method to test offsetSet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::offsetSet
	 */
	public function testOffsetSet()
	{
		$this->instance['bird'] = 'flying';

		$this->assertEquals($this->instance['bird'], 'flying');
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::offsetUnset
	 */
	public function testOffsetUnset()
	{
		unset($this->instance['bird']);

		$this->assertEquals($this->instance['bird'], null);
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::set
	 */
	public function testSet()
	{
		$this->instance->set('tree.bird', 'sleeping');

		$this->assertEquals($this->instance->get('tree.bird'), 'sleeping');
	}

	/**
	 * Method to test setRaw()
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::setRaw
	 */
	public function testSetRaw()
	{
		$object = (object) array('foo' => 'bar');

		$this->instance->setRaw('tree.bird', $object);

		$this->assertEquals('bar', $this->instance->get('tree.bird.foo'));
		$this->assertSame($object, $this->instance->get('tree.bird'));
	}

	/**
	 * Method to test toArray().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::toArray
	 */
	public function testToArray()
	{
		$registry = new Registry($this->getTestData());

		$this->assertEquals($registry->toArray(), $this->getTestData());
	}

	/**
	 * Method to test toObject().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::toObject
	 */
	public function testToObject()
	{
		$registry = new Registry($this->getTestData());

		$this->assertEquals($registry->toObject(), RegistryHelper::toObject($this->getTestData()));
	}

	/**
	 * Method to test toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::toString
	 */
	public function testToString()
	{
		$registry = new Registry($this->getTestData());

		$this->assertStringSafeEquals($this->loadFile(__DIR__ . '/Stubs/flower.ini'), $registry->toString('ini'));
		$this->assertStringSafeEquals($this->loadFile(__DIR__ . '/Stubs/flower.json'), $registry->toString('json'));
		$this->assertStringSafeEquals($this->loadFile(__DIR__ . '/Stubs/flower.yml'), $registry->toString('yml'));
		$this->assertStringSafeEquals($this->loadFile(__DIR__ . '/Stubs/flower.xml'), $registry->toString('xml'));
		$this->assertStringSafeEquals($this->loadFile(__DIR__ . '/Stubs/flower.php'), $registry->toString('php'));
	}

	/**
	 * Method to test flatten().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::flatten
	 */
	public function testFlatten()
	{
		$flatted = $this->instance->flatten();

		$this->assertEquals($flatted['pos1.sunflower'], 'love');

		$flatted = $this->instance->flatten('/');

		$this->assertEquals($flatted['pos1/sunflower'], 'love');
	}

	/**
	 * testAppend
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::push
	 */
	public function testPush()
	{
		$registry = new Registry;

		$registry->set('foo', array('var1', 'var2', 'var3'));

		$registry->push('foo', 'var4');

		$this->assertEquals('var4', $registry->get('foo.3'));

		$registry->push('foo', 'var5', 'var6');

		$this->assertEquals('var5', $registry->get('foo.4'));
		$this->assertEquals('var6', $registry->get('foo.5'));

		$registry->setRaw('foo2', (object) array('var1', 'var2', 'var3'));

		$b = $registry->get('foo2');

		$this->assertTrue(is_object($b));

		$registry->push('foo2', 'var4');

		$b = $registry->get('foo2');

		$this->assertTrue(is_array($b));
	}

	/**
	 * testShift
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::shift
	 */
	public function testShift()
	{
		$registry = new Registry;

		$registry->set('foo.bar', array('var1', 'var2', 'var3'));

		$this->assertEquals('var1', $registry->shift('foo.bar'));

		$this->assertEquals('var2', $registry->get('foo.bar.0'));

		$registry->setRaw('foo.bar2', (object) array('v1' => 'var1', 'v2' => 'var2', 'v3' => 'var3'));

		$this->assertEquals('var1', $registry->shift('foo.bar2'));

		$this->assertEquals('var2', $registry->get('foo.bar2.v2'));

		$this->assertTrue(is_array($registry->get('foo.bar2')));
	}

	/**
	 * testPop
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::pop
	 */
	public function testPop()
	{
		$registry = new Registry;

		$registry->set('foo.bar', array('var1', 'var2', 'var3'));

		$this->assertEquals('var3', $registry->pop('foo.bar'));

		$this->assertNull($registry->get('foo.bar.2'));

		$registry->setRaw('foo.bar2', (object) array('v1' => 'var1', 'v2' => 'var2', 'v3' => 'var3'));

		$this->assertEquals('var3', $registry->pop('foo.bar2'));

		$this->assertNull($registry->get('foo.bar2.v3'));

		$this->assertTrue(is_array($registry->get('foo.bar2')));
	}

	/**
	 * testUnshift
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Registry\Registry::unshift
	 */
	public function testUnshift()
	{
		$registry = new Registry;

		$registry->set('foo', array('var1', 'var2', 'var3'));

		$registry->unshift('foo', 'var4');

		$this->assertEquals('var4', $registry->get('foo.0'));

		$registry->unshift('foo', 'var5', 'var6');

		$this->assertEquals('var5', $registry->get('foo.0'));
		$this->assertEquals('var6', $registry->get('foo.1'));

		$registry->setRaw('foo2', (object) array('var1', 'var2', 'var3'));

		$b = $registry->get('foo2');

		$this->assertTrue(is_object($b));

		$registry->unshift('foo2', 'var4');

		$b = $registry->get('foo2');

		$this->assertTrue(is_array($b));
	}

	/**
	 * testReset
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Registry\Registry::reset
	 */
	public function testReset()
	{
		$this->instance->reset();

		$this->assertEquals(array(), $this->instance->getRaw());
	}

	/**
	 * testGetRaw
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Registry\Registry::getRaw
	 */
	public function testGetRaw()
	{
		$this->assertEquals($this->getTestData(), $this->instance->getRaw());
	}

	/**
	 * testGetIterator
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Registry\Registry::getIterator
	 */
	public function testGetIterator()
	{
		$this->assertInstanceOf('RecursiveArrayIterator', $this->instance->getIterator());

		$this->assertEquals($this->getTestData(), iterator_to_array($this->instance));
		$this->assertEquals(
			iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->getTestData()))),
			iterator_to_array(new \RecursiveIteratorIterator($this->instance))
		);
	}

	/**
	 * testCount
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Registry\Registry::count
	 */
	public function testCount()
	{
		$this->assertEquals(5, count($this->instance));
	}

	/**
	 * loadFile
	 *
	 * @param string $file
	 *
	 * @return  string
	 */
	protected function loadFile($file)
	{
		$text = file_get_contents($file);

		return $text;
	}
}
