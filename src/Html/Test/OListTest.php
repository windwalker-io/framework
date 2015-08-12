<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Enum\ListItem;
use Windwalker\Html\Enum\OList;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of OList
 *
 * @since 2.1
 */
class OListTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var OList
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
	 * testCreateList
	 *
	 * @return  void
	 */
	public function testCreateList()
	{
		$list = new OList;

		$this->assertEquals('<ol></ol>', (string) $list);

		$list = new OList(null, array('id' => 'list', 'class' => 'nav'));

		$this->assertEquals('<ol id="list" class="nav"></ol>', (string) $list);

		$items = array(
			new ListItem('Remember, with great power, comes great responsibility'),
			new ListItem('Life was like a box of chocolates.'),
			new ListItem('You mustn’t be afraid to dream a little bigger,darling.', array('class' => 'nav-item'))
		);

		$list = new OList($items);

		$html = <<<HTML
<ol>
	<li>Remember, with great power, comes great responsibility</li>
	<li>Life was like a box of chocolates.</li>
	<li class="nav-item">You mustn’t be afraid to dream a little bigger,darling.</li>
</ol>
HTML;

		$this->assertHtmlFormatEquals($html, (string) $list);
	}

	/**
	 * Method to test addItem().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Enum\AbstractHtmlList::addItem
	 */
	public function testAddItem()
	{
		$list = new OList;

		$list->addItem(new ListItem('123'))
			->addItem('ABC');

		$html = <<<HTML
<ol>
	<li>123</li>
	<li>ABC</li>
</ol>
HTML;

		$this->assertHtmlFormatEquals($html, $list);
	}

	/**
	 * testSetItems
	 *
	 * @return  void
	 */
	public function testSetItems()
	{
		$items = array(
			new ListItem('Remember, with great power, comes great responsibility'),
			new ListItem('Life was like a box of chocolates.'),
			new ListItem('You mustn’t be afraid to dream a little bigger,darling.', array('class' => 'nav-item'))
		);

		$list = new OList;
		$list->setItems($items);

		$html = <<<HTML
<ol>
	<li>Remember, with great power, comes great responsibility</li>
	<li>Life was like a box of chocolates.</li>
	<li class="nav-item">You mustn’t be afraid to dream a little bigger,darling.</li>
</ol>
HTML;

		$this->assertHtmlFormatEquals($html, (string) $list);
	}
}
