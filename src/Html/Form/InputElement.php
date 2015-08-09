<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Form;

use Windwalker\Dom\HtmlElement;

/**
 * The InputElement class.
 * 
 * @since  {DEPLOY_VERSION}
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
