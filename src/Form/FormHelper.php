<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form;

/**
 * The FormHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FormHelper
{
	public static function encode($html)
	{
		return htmlentities($html);
	}
}
 