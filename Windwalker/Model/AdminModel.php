<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

defined('JPATH_PLATFORM') or die;

/**
 * Prototype item model.
 *
 * @package     Joomla.Legacy
 * @subpackage  Model
 * @since       12.2
 */
abstract class AdminModel extends CrudModel
{
	/**
	 * Method to checkin a row.
	 *
	 * @param   integer $pk The numeric id of the primary key.
	 *
	 * @throws \Exception
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.2
	 */
	public function checkin($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if (!$pk)
		{
			return true;
		}

		$container = $this->getContainer();

		$user = $container->get('user');

		// Get an instance of the row to checkin.
		$table = $this->getTable();

		$table->load($pk);

		// Check if this is the user has previously checked out the row.
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
		{
			throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
		}

		// Attempt to check the row in.
		if (!$table->checkin($pk))
		{
			throw new \Exception($table->getError());
		}
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer $pk The numeric id of the primary key.
	 *
	 * @throws  \Exception
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.2
	 */
	public function checkout($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if (!$pk)
		{
			return true;
		}

		$container = $this->getContainer();

		$user = $container->get('user');

		// Get an instance of the row to checkout.
		$table = $this->getTable();

		$table->load($pk);

		// Check if this is the user having previously checked out the row.
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id'))
		{
			throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
		}

		// Attempt to check the row out.
		if (!$table->checkout($user->get('id'), $pk))
		{
			throw new \Exception($table->getError());
		}
	}
}
