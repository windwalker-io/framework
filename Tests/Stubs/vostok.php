<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Data\Tests;

use Joomla\Data\DataObject;

/**
 * Derived Data\DataObject class for testing.
 *
 * @since  1.0
 *
 * @method      launch() launch(string $status)
 */
class JDataVostok extends DataObject
{
	/**
	 * An array method.
	 *
	 * @param   string  $status  A method argument.
	 *
	 * @return  string  The return value for the method.
	 *
	 * @since   1.0
	 */
	public function launch($status)
	{
		return $status;
	}

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
		switch ($property)
		{
			case 'successful':
				$value = strtoupper($value);
				break;
		}

		return parent::setProperty($property, $value);
	}
}
