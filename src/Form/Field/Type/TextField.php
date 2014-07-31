<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

use Windwalker\Dom\HtmlElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The TextField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class TextField extends AbstractField
{
	/**
	 * getInput
	 *
	 * @return  string
	 */
	public function getInput()
	{
		// $attrs = $this->attributes;

		$attrs['name'] = $this->getFieldName();
		$attrs['class'] = $this->getAttribute('class');
		$attrs['type'] = 'text';

		return (string) new HtmlElement('input', null, $attrs);
	}
}
 