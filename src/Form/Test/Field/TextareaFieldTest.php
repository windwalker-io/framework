<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Form\Field\TextareaField;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class TextareaFieldTest extends AbstractDomTestCase
{
	/**
	 * Test instance.
	 *
	 * @var TextareaField
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
		$this->instance = new TextareaField(
			'flower',
			'Flower',
			[
				'class' => 'stub-flower'
			]
		);

		$this->instance->setAttribute('id',        'test-field');
		$this->instance->setAttribute('readonly',  true);
		$this->instance->setAttribute('disabled',  true);
		$this->instance->setAttribute('onchange',  'javascript:void(0);');
		$this->instance->setAttribute('cols',      10);
		$this->instance->rows(15);
		$this->instance->setAttribute('attribs',   ['data-test-element' => true]);

		$this->instance->setValue('sakura');
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
	 * @covers \Windwalker\Form\Field\TextField::prepareAttributes
	 */
	public function testRender()
	{
		$html = <<<HTML
<textarea name="flower" id="test-field" class="stub-flower" readonly="readonly" disabled="disabled" onchange="javascript:void(0);" cols="10" rows="15" data-test-element>sakura</textarea>
HTML;

		$this->assertDomStringEqualsDomString($html, $this->instance->renderInput());
	}
}
