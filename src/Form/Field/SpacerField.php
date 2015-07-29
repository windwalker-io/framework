<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\HtmlElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The SpacerField class.
 * 
 * @since  2.0
 */
class SpacerField extends AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'spacer';

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepare(&$attrs)
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
