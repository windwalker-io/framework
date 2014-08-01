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
 * The SpacerField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class SpacerField extends AbstractField
{
	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepareAttributes(&$attrs)
	{
		$attrs['id']    = $this->getAttribute('id', $this->getId());
		$attrs['class'] = $this->getAttribute('class');
	}

	/**
	 * buildInput
	 *
	 * @param array $attrs
	 *
	 * @return  mixed
	 */
	public function buildInput($attrs)
	{
		if ($this->getBool('hr'))
		{
			$node = 'hr';

			$content = null;
		}
		else
		{
			$node = 'span';

			$content = $this->getAttribute('description');
		}

		return new HtmlElement($node, $content, $attrs);
	}
}
 