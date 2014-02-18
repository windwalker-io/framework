<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Data\Tests;

use Joomla\Data\DataObject;

/**
 * Joomla Framework Capitaliser DataObject Class
 *
 * @since  1.0
 */
class JDataCapitaliser extends DataObject
{
	/**
	 * Set an object property.
	 *
	 * @param   string  $property  The property name.
	 * @param   mixed   $value     The property value.
	 *
	 * @return  mixed  The property value.
	 *
	 * @since   1.0
	 */
	protected function setProperty($property, $value)
	{
		return parent::setProperty($property, strtoupper($value));
	}
}
