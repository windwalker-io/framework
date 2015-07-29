<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Field\RadioField;
use Windwalker\Html\Option;
use Windwalker\Test\TestCase\DomTestCase;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class RadioFieldTest extends DomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var RadioField
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
		$this->instance = new RadioField(
			'flower',
			'Flower',
			array(
				new Option('Asia - Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
				new Option('Asia - Taipei', 'Asia/Taipei'),
				new Option('Europe - Paris', 'Asia/Paris'),
				new Option('UTC', 'UTC'),
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
<span id="flower" class="radio-inputs stub-flower">
	<input class="opt" value="Asia/Tokyo" name="flower" type="radio" id="flower-asia-tokyo" />
	<label class="opt" id="flower-asia-tokyo-label" for="flower-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" name="flower" type="radio" id="flower-asia-taipei" />
	<label id="flower-asia-taipei-label" for="flower-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" name="flower" type="radio" id="flower-asia-paris" />
	<label id="flower-asia-paris-label" for="flower-asia-paris">Europe - Paris</label>

	<input value="UTC" name="flower" type="radio" id="flower-utc" />
	<label id="flower-utc-label" for="flower-utc">UTC</label>
</span>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());

		$this->instance->setValue('UTC');

		$html = <<<HTML
<span id="flower" class="radio-inputs stub-flower">
	<input class="opt" value="Asia/Tokyo" name="flower" type="radio" id="flower-asia-tokyo" />
	<label class="opt" id="flower-asia-tokyo-label" for="flower-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" name="flower" type="radio" id="flower-asia-taipei" />
	<label id="flower-asia-taipei-label" for="flower-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" name="flower" type="radio" id="flower-asia-paris" />
	<label id="flower-asia-paris-label" for="flower-asia-paris">Europe - Paris</label>

	<input value="UTC" name="flower" checked="checked" type="radio" id="flower-utc" />
	<label id="flower-utc-label" for="flower-utc">UTC</label>
</span>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());
	}
}
