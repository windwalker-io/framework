<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Registry\Test;

use Windwalker\Registry\Helper\RegistryHelper;
use Windwalker\Registry\Registry;

/**
 * Test class of Registry
 *
 * @since {DEPLOY_VERSION}
 */
class RegistryTest extends \PHPUnit_Framework_TestCase
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

		$this->assertEquals($this->instance->get('lily'), 'love');
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
	 * @covers Windwalker\Registry\Registry::loadArray
	 */
	public function testLoadArray()
	{
		$registry = new Registry;

		$registry->loadArray($this->getTestData());

		$this->assertEquals($registry->get('olive'), 'peace');

		$this->assertEquals($registry->get('pos1.sunflower'), 'love');
	}

	/**
	 * Method to test loadObject().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::loadObject
	 */
	public function testLoadObject()
	{
		$registry = new Registry;

		$registry->loadObject((object) $this->getTestData());

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

		$this->assertEquals($registry->clear()->loadFile(__DIR__ . '/Stubs/flower.json', 'json')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadFile(__DIR__ . '/Stubs/flower.yml', 'yaml')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadFile(__DIR__ . '/Stubs/flower.ini', 'ini')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadFile(__DIR__ . '/Stubs/flower.xml', 'xml')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadFile(__DIR__ . '/Stubs/flower.php', 'php')->get('flower'), 'sakura');
	}

	/**
	 * Method to test loadString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::loadString
	 * @TODO   Implement testLoadString().
	 */
	public function testLoadString()
	{
		$registry = new Registry;

		$this->assertEquals($registry->clear()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.json'), 'json')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.yml'), 'yaml')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.ini'), 'ini')->get('flower'), 'sakura');
		$this->assertEquals($registry->clear()->loadString(file_get_contents(__DIR__ . '/Stubs/flower.xml'), 'xml')->get('flower'), 'sakura');
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
		$registry = new Registry(array('flower' => 'rose', 'honor' => 'Osmanthus month'));

		$registry->merge($this->instance, true);

		$this->assertEquals($registry->get('flower'), 'sakura');
		$this->assertEquals($registry->get('honor'), 'Osmanthus month');
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::offsetExists
	 * @TODO   Implement testOffsetExists().
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
	 * @TODO   Implement testOffsetGet().
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
	 * @TODO   Implement testOffsetSet().
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
	 * @TODO   Implement testOffsetUnset().
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
	 * @TODO   Implement testSet().
	 */
	public function testSet()
	{
		$this->instance->set('tree.bird', 'sleeping');

		$this->assertEquals($this->instance->get('tree.bird'), 'sleeping');
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

		$this->assertEquals($this->loadFile(__DIR__ . '/Stubs/flower.json'), $this->clean($registry->toString('json')));
		$this->assertEquals($this->loadFile(__DIR__ . '/Stubs/flower.yml'), $this->clean($registry->toString('yaml')));
		$this->assertEquals($this->loadFile(__DIR__ . '/Stubs/flower.ini'), $this->clean($registry->toString('ini')));
		$this->assertEquals($this->loadFile(__DIR__ . '/Stubs/flower.xml'), $this->clean($registry->toString('xml')));
		$this->assertEquals($this->loadFile(__DIR__ . '/Stubs/flower.php'), $this->clean($registry->toString('php')));
	}

	/**
	 * Method to test flatten().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Registry\Registry::toOneDimension
	 */
	public function flatten()
	{
		$flatted = $this->instance->flatten();

		$this->assertEquals($flatted['pos1.sunflower'], 'sakura');

		$flatted = $this->instance->flatten('/');

		$this->assertEquals($flatted['pos1/sunflower'], 'sakura');
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

		return $this->clean($text);
	}

	/**
	 * clean
	 *
	 * @param string $text
	 *
	 * @return  string
	 */
	protected function clean($text)
	{
		return trim(preg_replace('/\s+/', ' ', $text));
	}
}
