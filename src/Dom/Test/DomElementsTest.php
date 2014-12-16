<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\DomElement;
use Windwalker\Dom\DomElements;
use Windwalker\Dom\Helper\DomHelper;

/**
 * Test class of DomElements
 *
 * @since 2.0
 */
class DomElementsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var DomElements
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
		$elements = array(
			new DomElement('option', 'foo'),
			new DomElement('option', 'bar'),
			new DomElement('rdf:metaData', new DomElement('rdf:name', 'Simon')),
		);

		$this->instance = new DomElements($elements);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->instance = null;
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::__toString
	 */
	public function test__toString()
	{
		$expect = <<<DOM
<option>foo</option>
<option>bar</option>
<rdf:metaData>
	<rdf:name>
	Simon
	</rdf:name>
</rdf:metaData>
DOM;

		$this->assertEquals(
			DomHelper::minify($expect),
			DomHelper::minify($this->instance)
		);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::getIterator
	 */
	public function testGetIterator()
	{
		$iterator = $this->instance->getIterator();

		$this->assertInstanceOf('ArrayIterator', $iterator);

		$this->assertEquals(
			DomHelper::minify('<option>foo</option>'),
			DomHelper::minify($iterator[0])
		);
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance[1]));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::offsetGet
	 */
	public function testOffsetGet()
	{
		$this->assertEquals('rdf:metaData', $this->instance[2]->getName());
	}

	/**
	 * Method to test offsetSet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::offsetSet
	 */
	public function testOffsetSet()
	{
		$this->instance[0]->setName('foo:bar');

		$this->assertEquals('foo:bar', $this->instance[0]->getName());
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::offsetUnset
	 */
	public function testOffsetUnset()
	{
		unset($this->instance[2]);

		$this->assertNull($this->instance[2]);
	}

	/**
	 * Method to test count().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::count
	 */
	public function testCount()
	{
		$this->assertEquals(3, count($this->instance));
	}
}
