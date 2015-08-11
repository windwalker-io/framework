<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Form;

use Windwalker\Dom\HtmlElement;

/**
 * The InputElement class.
 * 
 * @since  2.1
 */
class InputElement extends HtmlElement
{
	/**
	 * Constructor
	 *
	 * @param string  $type
	 * @param string  $name
	 * @param array   $value
	 * @param array   $attribs
	 */
	public function __construct($type, $name, $value, $attribs = array())
	{
		$attribs['type']  = $type;
		$attribs['name']  = $name;
		$attribs['value'] = $value;

		parent::__construct('input', null, $attribs);
	}
}
