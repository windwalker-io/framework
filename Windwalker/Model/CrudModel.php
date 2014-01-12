<?php

namespace Windwalker\Model;

use JFactory;
use JFilterOutput;
use Joomla\DI\Container as JoomlaContainer;
use JTable;

/**
 * Class CrudModel
 *
 * @since 1.0
 */
class CrudModel extends FormModel
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
	 * Constructor
	 *
	 * @param   array              $config    An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   JoomlaContainer    $container Service container.
	 * @param   \JRegistry         $state     The model state.
	 * @param   \JDatabaseDriver   $db        The database adpater.
	 */
	public function __construct($config = array(), JoomlaContainer $container = null, \JRegistry $state = null, \JDatabaseDriver $db = null)
	{
		parent::__construct($config, $container, $state, $db);

		if (!$this->eventAfterDelete)
		{
			$this->eventAfterDelete = \JArrayHelper::getValue($config, 'event_after_delete', 'onContentAfterDelete');
		}

		if (!$this->eventBeforeDelete)
		{
			$this->eventBeforeDelete = \JArrayHelper::getValue($config, 'event_before_delete', 'onContentBeforeDelete');
		}

		if (!$this->eventAfterSave)
		{
			$this->eventAfterSave = \JArrayHelper::getValue($config, 'event_after_save', 'onContentAfterSave');
		}

		if (!$this->eventBeforeSave)
		{
			$this->eventBeforeSave = \JArrayHelper::getValue($config, 'event_before_save', 'onContentBeforeSave');
		}

		if (!$this->eventChangeState)
		{
			$this->eventChangeState = \JArrayHelper::getValue($config, 'event_change_state', 'onContentChangeState');
		}

		// @TODO: Check is needed or not.
		if (!$this->textPrefix)
		{
			$this->textPrefix = strtoupper(\JArrayHelper::getValue($config, 'text_prefix', $this->option));
		}
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
	 * @param   array $data The form data.
	 *
	 * @throws \Exception
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
		$pk  = \JArrayHelper::getValue($data, $key, $this->getState($this->getName() . '.id'));

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
		// Please override this method.
	}

	/**
	 * updateState
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return boolean
	 */
	public function updateState($pks, $data = array())
	{
		$dispatcher = $this->getContainer()->get('event.dispatcher');
		$errors  = array();
		$success = 0;
		$table   = $this->getTable();
		$pks     = (array) $pks;

		if (!count($pks))
		{
			return false;
		}

		// Include the content plugins for the change of state event.
		\JPluginHelper::importPlugin('content');

		$key = $table->getKeyName();

		// Update the state for rows with the given primary keys.
		foreach ($pks as $pk)
		{
			$table->reset();

			// Set primary
			$table->$key = $pk;

			// Bind data
			$table->bind($data);

			// Do save
			if (!$table->store())
			{
				$errors[] = $table->getError();

				continue;
			}

			$success++;
		}

		$this->state->set('error.message',  $errors);
		$this->state->set('success.number', $success);

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$dispatcher->trigger($this->eventChangeState, array($context, $pks, $data));

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$dispatcher = $this->getContainer()->get('event.dispatcher');
		$errors  = array();
		$success = 0;
		$pks     = (array) $pks;
		$table   = $this->getTable();

		// Include the content plugins for the on delete events.
		\JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if (!$table->load($pk))
			{
				$errors[] = $table->getError();

				continue;
			}

			$context = $this->option . '.' . $this->name;

			// Trigger the onContentBeforeDelete event.
			$result = $dispatcher->trigger($this->eventBeforeDelete, array($context, $table));

			if (in_array(false, $result, true))
			{
				$errors[] = $table->getError();

				continue;
			}

			if (!$table->delete($pk))
			{
				$errors[] = $table->getError();

				continue;
			}

			// Trigger the onContentAfterDelete event.
			$dispatcher->trigger($this->eventAfterDelete, array($context, $table));

			$success++;
		}

		$this->state->set('error.message',  $errors);
		$this->state->set('success.number', $success);

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
