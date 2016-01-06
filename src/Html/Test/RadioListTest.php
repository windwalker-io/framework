<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Option;
use Windwalker\Html\Select\RadioList;
use Windwalker\Dom\Test\AbstractDomTestCase;

/**
 * Test class of RadioList
 *
 * @since 2.0
 */
class RadioListTest extends AbstractDomTestCase
{
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
		$select = new RadioList(
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
<span class="radio-inputs input-select">
	<input class="opt" value="Asia/Tokyo" type="radio" name="form[timezone]" id="input-form-timezone-asia-tokyo" />
	<label class="opt" id="input-form-timezone-asia-tokyo-label" for="input-form-timezone-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" type="radio" name="form[timezone]" id="input-form-timezone-asia-taipei" />
	<label id="input-form-timezone-asia-taipei-label" for="input-form-timezone-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" type="radio" name="form[timezone]" id="input-form-timezone-asia-paris" />
	<label id="input-form-timezone-asia-paris-label" for="input-form-timezone-asia-paris">Europe - Paris</label>

	<input value="UTC" checked="checked" type="radio" name="form[timezone]" id="input-form-timezone-utc" />
	<label id="input-form-timezone-utc-label" for="input-form-timezone-utc">UTC</label>
</span>
HTML;

		$this->assertHtmlFormatEquals($expect, $select);
	}

	/**
	 * testCreateList
	 *
	 * @return  void
	 *
	 * Windwalker\Html\Select\SelectList::toString
	 */
	public function testCreateListWithDisabled()
	{
		$select = new RadioList(
			'form[timezone]',
			array(
				new Option('Asia - Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
				new Option('Asia - Taipei', 'Asia/Taipei'),
				new Option('Europe - Paris', 'Asia/Paris'),
				new Option('UTC', 'UTC'),
			),
			array('class' => 'input-select', 'disabled' => true, 'readonly' => true),
			'UTC',
			false
		);

		$expect = <<<HTML
<span class="radio-inputs input-select">
	<input class="opt" value="Asia/Tokyo" type="radio" name="form[timezone]" id="input-form-timezone-asia-tokyo" disabled="disabled" readonly="readonly" />
	<label class="opt" id="input-form-timezone-asia-tokyo-label" for="input-form-timezone-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" type="radio" name="form[timezone]" id="input-form-timezone-asia-taipei" disabled="disabled" readonly="readonly" />
	<label id="input-form-timezone-asia-taipei-label" for="input-form-timezone-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" type="radio" name="form[timezone]" id="input-form-timezone-asia-paris" disabled="disabled" readonly="readonly" />
	<label id="input-form-timezone-asia-paris-label" for="input-form-timezone-asia-paris">Europe - Paris</label>

	<input value="UTC" checked="checked" type="radio" name="form[timezone]" id="input-form-timezone-utc" disabled="disabled" readonly="readonly" />
	<label id="input-form-timezone-utc-label" for="input-form-timezone-utc">UTC</label>
</span>
HTML;

		$this->assertHtmlFormatEquals($expect, $select);
	}
}
