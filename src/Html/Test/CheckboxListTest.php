<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Html\Test;

use Windwalker\Html\Option;
use Windwalker\Html\Select\CheckboxList;
use Windwalker\Test\TestCase\AbstractDomTestCase;

/**
 * Test class of CheckboxList
 *
 * @since 2.0
 */
class CheckboxListTest extends AbstractDomTestCase
{
	/**
	 * testCreateList
	 *
	 * @return  void
	 *
	 * Windwalker\Html\Select\SelectList::toString
	 */
	public function testCreateList()
	{
		$select = new CheckboxList(
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
<span class="checkbox-inputs input-select">
	<input class="opt" value="Asia/Tokyo" type="checkbox" name="form[timezone][]" id="form-timezone-asia-tokyo" />
	<label class="opt" id="form-timezone-asia-tokyo-label" for="form-timezone-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" type="checkbox" name="form[timezone][]" id="form-timezone-asia-taipei" />
	<label id="form-timezone-asia-taipei-label" for="form-timezone-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" type="checkbox" name="form[timezone][]" id="form-timezone-asia-paris" />
	<label id="form-timezone-asia-paris-label" for="form-timezone-asia-paris">Europe - Paris</label>

	<input value="UTC" checked="checked" type="checkbox" name="form[timezone][]" id="form-timezone-utc" />
	<label id="form-timezone-utc-label" for="form-timezone-utc">UTC</label>
</span>
HTML;

		$this->assertHtmlFormatEquals($expect, $select);
	}
}
