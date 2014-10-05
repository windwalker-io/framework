<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Select;

use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;
use Windwalker\Html\Option;

/**
 * The CheckboxList class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class CheckboxList extends AbstractInputList
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'checkbox';

	/**
	 * prepareOptions
	 *
	 * @return  void
	 */
	protected function prepareOptions()
	{
		parent::prepareOptions();

		// Prepare array name
		foreach ($this->content as $key => &$option)
		{
			$option[0]->setAttribute('name', $option[0]->getAttribute('name') . '[]');
		}
	}

	/**
	 * isChecked
	 *
	 * @param  Option $option
	 *
	 * @return  bool
	 */
	protected function isChecked(Option $option)
	{
		return in_array($option->getValue(), (array) $this->getChecked());
	}
}
