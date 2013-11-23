<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Base
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * A Proxy for JText, and auto add 'COM_COMPONENT_' before every language key.
 *
 * @package     Windwalker.Framework
 * @subpackage  Base
 * @since       2.5
 */
class AKText
{
	/**
	 * Magic method to call all method exists in JText, and add 'COM_COMPONENT_' before every language key.
	 * Not recommend use this class now.
	 *
	 * @param   string  $name  The method name.
	 * @param   array   $args  All arguements.
	 *
	 * @return  mixed   Return from JText methods.
	 */
	public static function __callStatic($name, $args)
	{
		$option = AKHelper::_('path.getOption');

		if (!$option)
		{
			$option = JRequest::getVar('option');
		}

		$args[0] = strtoupper(strtoupper($option) . '_' . $args[0]);

		return call_user_func_array(array('JText', $name), $args);
	}
}
