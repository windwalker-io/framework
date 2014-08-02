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
		foreach ($this->content as &$option)
		{
			if (in_array($option->getValue(), (array) $this->getChecked()))
			{
				$option['checked'] = 'checked';
			}

			$attrs = $option->getAttributes();

			$label = $this->createLabel($option);

			$attrs['type'] = $this->type;

			$input = new HtmlElement('input', '', $attrs);

			$option = new HtmlElements(array($input, $label));
		}
	}
}
