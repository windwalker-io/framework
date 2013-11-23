<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include_once AKPATH_COMPONENT . '/modeladmin.php';

/**
 * API Response Model for ModelAdmin & Item.
 */
class AKResponseModelItem extends AKModelAdmin
{
	public $default_method;

	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 * @since   11.1
	 */
	public function save($data)
	{
		$data              = JRequest::get('post');
		$data['introtext'] = $_POST['introtext'];

		$result = new JObject();

		// Execute parent
		if (parent::save($data))
		{
			$result->success = true;
			$result->item    = $this->getItem();
			$this->checkin($data->id);
		}
		else
		{
			$result->success  = false;
			$result->item     = null;
			$result->errorMsg = $this->getError();
		}

		return $result;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since   11.1
	 */
	public function delete(&$pks = null)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		// Execute parent
		$result = parent::delete($pks);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array   &$pks  A list of the primary keys to change.
	 * @param   integer $value The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since   11.1
	 */
	public function publish(&$pks = null, $value = 1)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		$val = JRequest::getVar('value', $value);

		// Execute parent
		$result = parent::publish($pks, $val);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array $commands An array of commands to perform.
	 * @param   array $pks      An array of item ids.
	 * @param   array $contexts An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 * @since   11.1
	 */
	public function batch($commands = null, $pks = null, $contexts = null)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		$commands = JRequest::getVar('commands', $commands);

		// build Contexts
		foreach ($pks as $pk):
			$contexts[$pk] = $option . '.' . $this->item_name . '.' . $id;
		endforeach;

		// Execute parent
		$result = parent::batch($commands, $pks, $contexts);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method override to check-in a record or an array of record
	 *
	 * @param   mixed $pks The ID of the primary key or an array of IDs
	 *
	 * @return  mixed  Boolean false if there is an error, otherwise the count of records checked in.
	 * @since   11.1
	 */
	public function checkin($pks = array())
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		// Execute parent
		$result = parent::checkin($pks);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method override to check-out a record.
	 *
	 * @param   integer $pk The ID of the primary key.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		$pk = $pk ? $pk : JRequest::getVar('id');

		// Execute parent
		$result = parent::checkout($pk);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method to adjust the ordering of a row.
	 * Returns NULL if the user did not have edit
	 * privileges for any of the selected primary keys.
	 *
	 * @param   integer $pks   The ID of the primary key to move.
	 * @param   integer $delta Increment, usually +1 or -1
	 *
	 * @return  mixed  False on failure or error, true on success, null if the $pk is empty (no items selected).
	 * @since   11.1
	 */
	public function reorder($pks, $delta = 0)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		$delta = JRequest::getVar('delta', $delta);

		// Execute parent
		$result = parent::reorder($pks, $delta);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array   $pks   An array of primary key ids.
	 * @param   integer $order +1 or -1
	 *
	 * @return  mixed
	 * @since   11.1
	 */
	public function saveorder($pks = null, $order = null)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		$order = JRequest::getVar('order', $order);

		// Execute parent
		$result = parent::saveorder($pks, $order);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * Method to duplicate items.
	 *
	 * @param   array &$pks An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks = null)
	{
		// make keys to array
		$pks = $this->_getPrimaryKeys($pks);

		// Execute parent
		$result = parent::duplicate($pks);

		// Build Return Object
		return $this->_setReturnValue($result);
	}

	/**
	 * function _getPrimaryKeys
	 *
	 * @param $pk
	 */
	public function _getPrimaryKeys($pks)
	{
		if (!$pks)
		{
			$pks = JRequest::getVar('id', JRequest::getVar('cid'));
			$pks = $pks ? (array) $pks : JRequest::getVar('cid', array());
		}

		return $pks;
	}

	/**
	 * function _setReturnValue
	 *
	 * @param $result
	 */
	public function _setReturnValue($result)
	{
		$return = new JObject();

		if ($result !== false)
		{
			$return->success = true;
		}
		else
		{
			$return->success  = false;
			$return->errorMsg = $this->getError();
		}

		return $return;
	}
}