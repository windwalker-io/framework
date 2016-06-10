<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Field\ListField;
use Windwalker\Html\Option;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class ListFieldTest extends AbstractDomTestCase
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
				new \Windwalker\Html\Option('', '')
			),
			array(
				'class' => 'stub-flower'
			)
		);

		$this->instance->option(1, 'Yes')
			->option(0, 'No');

		$this->instance->setAttribute('size',     10);
		$this->instance->setAttribute('readonly', false);
		$this->instance->setAttribute('disabled', true);
		$this->instance->setAttribute('onchange', 'return false;');
		$this->instance->setAttribute('multiple', false);
		$this->instance->setAttribute('attribs',  array('data-test-element' => true));
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
<select name="flower" id="input-flower" class="stub-flower" size="10" disabled="disabled" onchange="return false;" data-test-element>
	<option value="" selected="selected"></option>
	<option value="Yes">1</option>
	<option value="No">0</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());

		$this->instance->setValue(1);

		$html = <<<HTML
<select name="flower" id="input-flower" class="stub-flower" size="10" disabled="disabled" onchange="return false;" data-test-element>
	<option value="" selected="selected"></option>
	<option value="Yes">1</option>
	<option value="No">0</option>
</select>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());

		$this->instance->setAttribute('multiple', true);

		$html = <<<HTML
<select name="flower[]" id="input-flower" class="stub-flower" size="10" disabled="disabled" onchange="return false;" multiple="true" data-test-element>
	<option value="" selected="selected"></option>
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
				)
			)
		);

		$field->group('Europe', function (ListField $field)
		{
			$field->option('Paris', 'Europe/Paris');
		})->option('UTC', 'UTC');

		$html = <<<HTML
<select name="timezone" id="input-timezone">
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
