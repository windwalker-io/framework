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
abstract class AdminModel extends FormModel
{
	/**
	 * An item.
	 *
	 * @var    array
	 */
	protected $item = null;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $textPrefix = null;

	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventAfterDelete = null;

	/**
	 * The event to trigger after saving the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventAfterSave = null;

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventBeforeDelete = null;

	/**
	 * The event to trigger before saving the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventBeforeSave = null;

	/**
	 * The event to trigger after changing the published state of the data.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $eventChangeState = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModel
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->eventAfterDelete  = \JArrayHelper::getValue($config, 'event_after_delete', 'onContentAfterDelete');

		$this->eventBeforeDelete = \JArrayHelper::getValue($config, 'event_before_delete', 'onContentBeforeDelete');

		$this->eventAfterSave    = \JArrayHelper::getValue($config, 'event_after_save', 'onContentAfterSave');

		$this->eventBeforeSave   = \JArrayHelper::getValue($config, 'event_before_save', 'onContentBeforeSave');

		$this->eventChangeState  = \JArrayHelper::getValue($config, 'event_change_state', 'onContentChangeState');

		// @TODO: Check is needed or not.
		$this->textPrefix = strtoupper(\JArrayHelper::getValue($config, 'text_prefix', $this->option));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = \JFactory::getApplication()->input->getInt($key);
		$this->state->set($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = \JComponentHelper::getParams($this->option);
		$this->state->set('params', $value);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->state->get($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$table->load($pk);
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = \JArrayHelper::toObject($properties, 'stdClass');

		if (property_exists($item, 'params'))
		{
			$registry = new \JRegistry;

			$registry->loadString($item->params);

			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		$container  = $this->getContainer();
		$table      = $this->getTable();
		$dispatcher = $container->get('event.dispatcher');

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = \JArrayHelper::getValue($data, $key, $this->getState($this->getName() . '.id'));

		$isNew = true;

		// Include the content plugins for the on save events.
		\JPluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		$table->bind($data);

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			throw new \Exception($table->getError());
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->eventBeforeSave, array($this->option . '.' . $this->name, $table, $isNew));

		if (in_array(false, $result, true))
		{
			throw new \Exception($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new \Exception($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->eventAfterSave, array($this->option . '.' . $this->name, $table, $isNew));

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->state->set($this->getName() . '.id', $table->$pkName);
		}

		$this->state->set($this->getName() . '.new', $isNew);

		return true;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function prepareTable($table)
	{
		// Derived class will provide its own implementation if required.
	}

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

	/**
	 * updateState
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return boolean
	 */
	public function updateState($pks, $field, $value)
	{
		$dispatcher = $this->getContainer()->get('event.dispatcher');
		$user  = \JFactory::getUser();
		$query = $this->db->getQuery(true);
		$table = $this->getTable();
		$pks   = (array) $pks;

		if (!count($pks))
		{
			return false;
		}

		// Include the content plugins for the change of state event.
		\JPluginHelper::importPlugin('content');

		// Update the state for rows with the given primary keys.
		$query->update($table->getTableName())
			->set($query->quoteName($field) . ' = ' . $query->quote($value))
			->where($query->quoteName($table->getKeyName()) . ' IN (' . implode(',', $pks) . ')');

		if (!$this->db->setQuery($query)->execute())
		{
			throw new \Exception($this->db->getError());
		}
		
		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->eventChangeState, array($context, $pks, $value));

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
