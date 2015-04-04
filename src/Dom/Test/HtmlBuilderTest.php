<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\Dom\Helper\DomHelper;

/**
 * Test class of HtmlBuilder
 *
 * @since 2.0
 */
class HtmlBuilderTest extends \PHPUnit_Framework_TestCase
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
				'case1_anchor_single_tag',
				'<a />',
				'a',
				null,
				array(),
				false
			),
			array(
				'case2_div',
				'<div>Some Data</div>',
				'div',
				'Some Data',
				array(),
				false
			),
			array(
				'case3_div_no_content',
				'<div id="foo" class="bar"></div>',
				'div',
				null,
				array('id' => 'foo', 'class' => 'bar'),
				false
			),
			array(
				'case4_ul',
				'<ul id="foo" class="bar">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</ul>',
				'ul',
				HtmlBuilder::create('option', 'Yes', array('value' => 1))
				. HtmlBuilder::create('option', 'No', array('value' => 0)),
				array('id' => 'foo', 'class' => 'bar'),
				false
			),
			array(
				'case5_force_paired',
				'<a></a>',
				'a',
				null,
				array(),
				true
			),
			array(
				'case6_ul',
				'<hr />',
				'hr',
				null,
				array(),
				false
			),
			array(
				'case7_empty_content',
				'<hr></hr>',
				'hr',
				'',
				array(),
				false
			),
			array(
				'case8_attr_only_tag',
				'<video controls muted></video>',
				'video',
				'',
				array('controls' => true, 'muted' => true),
				false
			)
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
	 * @covers       Windwalker\Dom\Builder\HtmlBuilder::create
	 *
	 * @dataProvider domTestCase
	 */
	public function testCreate($name, $expect, $tag, $content, $attribs, $forcePaired)
	{
		$this->assertEquals(
			DomHelper::minify($expect),
			DomHelper::minify(HtmlBuilder::create($tag, $content, $attribs, $forcePaired)),
			'Dom build case fail: ' . $name
		);
	}
}
