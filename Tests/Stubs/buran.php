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
 */
class JDataBuran extends DataObject
{
	public $rocket = false;

	/**
	 * Method to set the test_value.
	 *
	 * @param   string  $value  The test value.
	 *
	 * @return  Object  Chainable.
	 *
	 * @since   1.0
	 */
	protected function setTestValue($value)
	{
		// Set the property as uppercase.
		return strtoupper($value);
	}
}
