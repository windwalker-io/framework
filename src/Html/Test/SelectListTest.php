<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of SelectList
 *
 * @since 2.0
 */
class SelectListTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var SelectList
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
		// $this->instance = new SelectList;
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
	 *
	 * Windwalker\Html\Select\SelectList::toString
	 */
	public function testCreateList()
	{
		$select = new SelectList(
			'form[timezone]',
			array(
				new Option('Asia - Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
				new Option('Asia - Taipei', 'Asia/Taipei'),
				new Option('Europe - Paris', 'Asia/Paris'),
				new Option('UTC', 'UTC'),
			),
			array('class' => 'input-select'),
			'UTC',
			false
		);

		$expect = <<<HTML
<select class="input-select" name="form[timezone]">
	<option class="opt" value="Asia/Tokyo">Asia - Tokyo</option>
	<option value="Asia/Taipei">Asia - Taipei</option>
	<option value="Asia/Paris">Europe - Paris</option>
	<option value="UTC" selected="selected">UTC</option>
</select>
HTML;

		$this->assertHtmlFormatEquals($expect, $select);

		$expect = <<<HTML
<select class="input-select" name="form[timezone]">
	<option class="opt" value="Asia/Tokyo">Asia - Tokyo</option>
	<option value="Asia/Taipei">Asia - Taipei</option>
	<option value="Asia/Paris">Europe - Paris</option>
	<option value="UTC" selected="selected">UTC</option>
	<option value="Europe/London">Europe - London</option>
</select>
HTML;

		$select->addOption(new Option('Europe - London', 'Europe/London'));

		$this->assertHtmlFormatEquals($expect, $select);
	}

	/**
	 * testCreateList
	 *
	 * @return  void
	 *
	 * Windwalker\Html\Select\SelectList::toString
	 */
	public function testCreateGroupList()
	{
		$select = new SelectList(
			'form[timezone]',
			array(
				'Asia' => array(
					new Option('Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
					new Option('Taipei', 'Asia/Taipei')
				),
				'Europe' => array(
					new Option('Europe - Paris', 'Asia/Paris')
				)
				,
				new Option('UTC', 'UTC'),
			),
			array('class' => 'input-select'),
			'UTC',
			false
		);

		$expect = <<<HTML
<select class="input-select" name="form[timezone]">
	<optgroup label="Asia">
		<option class="opt" value="Asia/Tokyo">Tokyo</option>
		<option value="Asia/Taipei">Taipei</option>
	</optgroup>

	<optgroup label="Europe">
		<option value="Asia/Paris">Europe - Paris</option>
	</optgroup>

	<option value="UTC" selected="selected">UTC</option>
</select>
HTML;

		$this->assertHtmlFormatEquals($expect, $select);

		$expect = <<<HTML
<select class="input-select" name="form[timezone]">
	<optgroup label="Asia">
		<option class="opt" value="Asia/Tokyo">Tokyo</option>
		<option value="Asia/Taipei">Taipei</option>
	</optgroup>

	<optgroup label="Europe">
		<option value="Asia/Paris">Europe - Paris</option>
		<option value="Europe/London">Europe - London</option>
	</optgroup>

	<option value="UTC" selected="selected">UTC</option>
</select>
HTML;
		$select->addOption(new Option('Europe - London', 'Europe/London'), 'Europe');

		$this->assertHtmlFormatEquals($expect, $select);
	}

	/**
	 * Method to test getSelected().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Select\SelectList::getSelected
	 * @TODO   Implement testGetSelected().
	 */
	public function testGetSelected()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setSelected().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Select\SelectList::setSelected
	 * @TODO   Implement testSetSelected().
	 */
	public function testSetSelected()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
