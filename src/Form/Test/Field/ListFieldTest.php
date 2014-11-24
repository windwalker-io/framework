<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Field\ListField;
use Windwalker\Html\Option;
use Windwalker\Test\TestCase\DomTestCase;

/**
 * Test class of TextField
 *
 * @since {DEPLOY_VERSION}
 */
class ListFieldTest extends DomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var ListField
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
		$this->instance = new ListField(
			'flower',
			'Flower',
			array(
				new \Windwalker\Html\Option('', ''),
				new \Windwalker\Html\Option(1, 'Yes'),
				new \Windwalker\Html\Option(0, 'No'),
			),
			array(
				'class' => 'stub-flower'
			)
		);

		$this->instance->setAttribute('size',     10);
		$this->instance->setAttribute('readonly', false);
		$this->instance->setAttribute('disabled', true);
		$this->instance->setAttribute('onchange', 'return false;');
		$this->instance->setAttribute('multiple', false);
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
	 * Method to test prepareAttributes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\TextField::prepareAttributes
	 */
	public function testRender()
	{
		$html = <<<HTML
<select name="flower" id="flower" class="stub-flower" size="10" disabled="true" onchange="return false;">
	<option selected="selected"></option>
	<option value="Yes">1</option>
	<option value="No">0</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());

		$this->instance->setValue(1);

		$html = <<<HTML
<select name="flower" id="flower" class="stub-flower" size="10" disabled="true" onchange="return false;">
	<option selected="selected"></option>
	<option value="Yes">1</option>
	<option value="No">0</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());

		$this->instance->setAttribute('multiple', true);

		$html = <<<HTML
<select name="flower" id="flower" class="stub-flower" size="10" disabled="true" onchange="return false;" multiple="true">
	<option selected="selected"></option>
	<option value="Yes">1</option>
	<option value="No">0</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());
	}

	/**
	 * Method to test prepareAttributes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Form\Field\TextField::prepareAttributes
	 */
	public function testRenderGroup()
	{
		$field = new ListField(
			'timezone',
			'Time Zone',
			array(
				'Asia' => array(
					new Option('Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
					new Option('Taipei', 'Asia/Taipei')
				),
				'Europe' => array(
					new Option('Paris', 'Europe/Paris')
				)
				,
				new Option('UTC', 'UTC'),
			)
		);

		$html = <<<HTML
<select name="timezone" id="timezone">
	<optgroup label="Asia">
		<option class="opt" value="Asia/Tokyo">Tokyo</option>
		<option value="Asia/Taipei">Taipei</option>
	</optgroup>

	<optgroup label="Europe">
		<option value="Europe/Paris">Paris</option>
	</optgroup>

	<option value="UTC">UTC</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $field->renderInput());
	}
}
