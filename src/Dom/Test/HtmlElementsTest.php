<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Helper\DomHelper;
use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;

/**
 * Test class of HtmlElements
 *
 * @since 2.0
 */
class HtmlElementsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var HtmlElements
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
			new HtmlElement('p', 'foo'),
			new HtmlElement('table', new HtmlElement('tr', new HtmlElement('td', 'bar'))),
			new HtmlElement('div', 'yoo', array('id' => 'fly')),
		);

		$this->instance = new HtmlElements($elements);
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
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Dom\DomElements::__toString
	 */
	public function test__toString()
	{
		$expect = <<<DOM
<p>foo</p>
<table>
	<tr>
		<td>bar</td>
	</tr>
</table>
<div id="fly">yoo</div>
DOM;

		$this->assertEquals(
			DomHelper::minify($expect),
			DomHelper::minify($this->instance)
		);
	}
}
