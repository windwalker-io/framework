<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\SimpleXml\XmlHelper;

/**
 * Test class of XmlHelper
 *
 * @since 2.0
 */
class XmlHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var \SimpleXMLElement
	 */
	protected $xml;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$xml = <<<XML
<root>
	<field
		name="List"
		type="list"
		label="List"
		description="list desc"
		class="sun"
		default="b"

		required="true"
		disabled="false"

		boolTrue1="1"
		boolTrue2="yes"
		boolTrue3="true"

		boolFalse1="0"
		boolFalse2="no"
		boolFalse3="null"
		boolFalse4="none"
		boolFalse5="disabled"
		>
		<option></option>
		<option value="y">Yes</option>
		<option value="n">No</option>
	</field>
</root>
XML;

		$xml = simplexml_load_string($xml);

		$this->xml = $xml->field;
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
	 * Method to test getAttribute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\SimpleXml\XmlHelper::getAttribute
	 */
	public function testGetAttribute()
	{
		$this->assertEquals('sun', XmlHelper::getAttribute($this->xml, 'class'));
		$this->assertEquals('default', XmlHelper::getAttribute($this->xml, 'cloud', 'default'));
		$this->assertEquals(null, XmlHelper::getAttribute($this->xml, 'cloud'));
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\SimpleXml\XmlHelper::get
	 */
	public function testGet()
	{
		$this->assertEquals('sun', XmlHelper::get($this->xml, 'class'));
		$this->assertEquals('default', XmlHelper::get($this->xml, 'cloud', 'default'));
		$this->assertEquals(null, XmlHelper::get($this->xml, 'cloud'));
	}

	/**
	 * boolCases
	 *
	 * @return  array
	 */
	public function boolCases()
	{
		return array(
			array(
				1,
				'required',
				true,
				null
			),
			array(
				2,
				'disabled',
				false,
				null
			),
			array(
				3,
				'boolTrue1',
				true,
				null
			),
			array(
				4,
				'boolTrue2',
				true,
				null
			),
			array(
				5,
				'boolTrue3',
				true,
				null
			),
			array(
				6,
				'boolFalse1',
				false,
				null
			),
			array(
				7,
				'boolFalse2',
				false,
				null
			),
			array(
				8,
				'boolFalse3',
				false,
				null
			),
			array(
				10,
				'boolFalse4',
				false,
				null
			),
			array(
				11,
				'boolFalse5',
				false,
				null
			),
			array(
				'12_default',
				'flower',
				false,
				false
			)
		);
	}

	/**
	 * Method to test getBool().
	 *
	 * @param string|int $id
	 * @param string     $name
	 * @param boolean    $expect
	 * @param boolean    $default
	 *
	 * @return void
	 *
	 * @covers       Windwalker\Dom\SimpleXml\XmlHelper::getBool
	 *
	 * @dataProvider boolCases
	 */
	public function testGetBool($id, $name, $expect, $default)
	{
		$this->assertEquals($expect, XmlHelper::getBool($this->xml, $name, $default), 'Case fail: case_' . $id);
	}

	/**
	 * Method to test getFalse().
	 *
	 * @param string|int $id
	 * @param string     $name
	 * @param boolean    $expect
	 * @param boolean    $default
	 *
	 * @return void
	 *
	 * @covers       Windwalker\Dom\SimpleXml\XmlHelper::getFalse
	 *
	 * @dataProvider boolCases
	 */
	public function testGetFalse($id, $name, $expect, $default)
	{
		$this->assertEquals(!$expect, XmlHelper::getFalse($this->xml, $name, $default), 'Case fail: case_' . $id);
	}

	/**
	 * Method to test getAttributes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\SimpleXml\XmlHelper::getAttributes
	 */
	public function testGetAttributes()
	{
		$attributes = array();

		foreach ($this->xml->attributes() as $name => $value)
		{
			$attributes[$name] = (string) $value;
		}

		$this->assertEquals($attributes, XmlHelper::getAttributes($this->xml));
	}

	/**
	 * Method to test def().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\SimpleXml\XmlHelper::def
	 */
	public function testDef()
	{
		XmlHelper::def($this->xml, 'flower', 'rose');
		XmlHelper::def($this->xml, 'name', 'Play');

		$this->assertEquals('rose', XmlHelper::get($this->xml, 'flower'));
		$this->assertEquals('List', XmlHelper::get($this->xml, 'name'));
	}
}
