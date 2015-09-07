<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Builder\DomBuilder;
use Windwalker\Dom\Helper\DomHelper;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of DomBuilder
 *
 * @since 2.0
 */
class DomBuilderTest extends AbstractDomTestCase
{
	/**
	 * domTestCase
	 *
	 * @return  array
	 */
	public function domTestCase()
	{
		return array(
			array(
				'case1',
				'<field />',
				'field',
				null,
				array(),
				false
			),
			array(
				'case2',
				'<field>Some Data</field>',
				'field',
				'Some Data',
				array(),
				false
			),
			array(
				'case3',
				'<field id="foo" class="bar" />',
				'field',
				null,
				array('id' => 'foo', 'class' => 'bar'),
				false
			),
			array(
				'case4',
				'<field id="foo" class="bar">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</field>',
				'field',
				DomBuilder::create('option', 'Yes', array('value' => 1))
				. DomBuilder::create('option', 'No', array('value' => 0)),
				array('id' => 'foo', 'class' => 'bar'),
				false
			),
			array(
				'case5_force_paired',
				'<field></field>',
				'field',
				null,
				array(),
				true
			),
		);
	}

	/**
	 * Method to test create().
	 *
	 * @param string  $name
	 * @param string  $expect
	 * @param string  $tag
	 * @param string  $content
	 * @param array   $attribs
	 * @param boolean $forcePaired
	 *
	 * @return void
	 *
	 * @covers       Windwalker\Dom\Builder\DomBuilder::create
	 *
	 * @dataProvider domTestCase
	 */
	public function testCreate($name, $expect, $tag, $content, $attribs, $forcePaired)
	{
		$this->assertDomFormatEquals(
			$expect,
			DomBuilder::create($tag, $content, $attribs, $forcePaired),
			'Dom build case fail: ' . $name
		);
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\Builder\DomBuilder::quote
	 */
	public function testQuote()
	{
		$this->assertEquals('"foo"', DomBuilder::quote('foo'));
	}

	/**
	 * testPrepareAttributes
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Dom\Builder\DomBuilder::buildAttributes
	 */
	public function testBuildAttributes()
	{
		$attrs = array(
			'foo' => 'bar',
			'data' => true,
			'bar' => false,
			'empty' => '',
			'selected' => true,
			'checked' => false
		);

		$this->assertEquals(' foo="bar" data empty="" selected', DomBuilder::buildAttributes($attrs));
	}
}
