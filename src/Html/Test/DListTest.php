<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Enum\DList;
use Windwalker\Html\Enum\DListDescription;
use Windwalker\Html\Enum\DListTitle;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of DList
 *
 * @since 2.1
 */
class DListTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var DList
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
		$list = new DList;

		$this->assertEquals('<dl></dl>', (string) $list);

		$list = new DList(null, array('id' => 'list', 'class' => 'nav'));

		$this->assertEquals('<dl id="list" class="nav"></dl>', (string) $list);

		$items = array(
			new DListTitle('Spider Man'),
			new DListDescription('Remember, with great power, comes great responsibility'),
			new DListTitle('Forrest Gump'),
			new DListDescription('Life was like a box of chocolates.'),
			new DListTitle('Inception'),
			new DListDescription('You mustn’t be afraid to dream a little bigger,darling.', array('class' => 'nav-item'))
		);

		$list = new DList($items);

		$html = <<<HTML
<dl>
	<dt>Spider Man</dt>
	<dd>Remember, with great power, comes great responsibility</dd>

	<dt>Forrest Gump</dt>
	<dd>Life was like a box of chocolates.</dd>

	<dt>Inception</dt>
	<dd class="nav-item">You mustn’t be afraid to dream a little bigger,darling.</dd>
</dl>
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
		$list = new DList;

		$list->addTitle(new DListTitle('123'))
			->addDesc('ABC');

		$html = <<<HTML
<dl>
	<dt>123</dt>
	<dd>ABC</dd>
</dl>
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
			new DListTitle('Spider Man'),
			new DListDescription('Remember, with great power, comes great responsibility'),
			new DListTitle('Forrest Gump'),
			new DListDescription('Life was like a box of chocolates.'),
			new DListTitle('Inception'),
			new DListDescription('You mustn’t be afraid to dream a little bigger,darling.', array('class' => 'nav-item'))
		);

		$list = new DList;
		$list->setItems($items);

		$html = <<<HTML
<dl>
	<dt>Spider Man</dt>
	<dd>Remember, with great power, comes great responsibility</dd>

	<dt>Forrest Gump</dt>
	<dd>Life was like a box of chocolates.</dd>

	<dt>Inception</dt>
	<dd class="nav-item">You mustn’t be afraid to dream a little bigger,darling.</dd>
</dl>
HTML;

		$this->assertHtmlFormatEquals($html, (string) $list);
	}

	/**
	 * testAddDescription
	 *
	 * @return  void
	 */
	public function testAddDescription()
	{
		$list = new DList;

		$list->addDescription('Spider Man', 'Remember, with great power, comes great responsibility')
			->addDescription('Forrest Gump', 'Life was like a box of chocolates.')
			->addDescription('Inception', 'You mustn’t be afraid to dream a little bigger,darling.', array(), array('class' => 'nav-item'));

		$html = <<<HTML
<dl>
	<dt>Spider Man</dt>
	<dd>Remember, with great power, comes great responsibility</dd>

	<dt>Forrest Gump</dt>
	<dd>Life was like a box of chocolates.</dd>

	<dt>Inception</dt>
	<dd class="nav-item">You mustn’t be afraid to dream a little bigger,darling.</dd>
</dl>
HTML;

		$this->assertHtmlFormatEquals($html, (string) $list);
	}
}
