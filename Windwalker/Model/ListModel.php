<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use Joomla\DI\Container as JoomlaContainer;
use Windwalker\Model\Filter\FilterHelper;
use Windwalker\Model\Filter\FilterProvider;
use Windwalker\Model\Filter\SearchHelper;

defined('JPATH_PLATFORM') or die;

/**
 * Model class for handling lists of items.
 *
 * @package     Joomla.Legacy
 * @subpackage  Model
 * @since       12.2
 */
class ListModel extends FormModel
{
	/**
	 * Internal memory based cache array of data.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $cache = array();

	/**
	 * Valid filter fields or ordering.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $filterFields = array();

	/**
	 * An internal cache for the last query used.
	 *
	 * @var    \JDatabaseQuery
	 * @since  12.2
	 */
	protected $query = array();

	/**
	 * Name of the filter form to load
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $formPath = null;

	/**
	 * Property forms.
	 *
	 * @var
	 */
	protected $forms;

	/**
	 * Property orderCol.
	 *
	 * @var string
	 */
	protected $orderCol = null;

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
		// These need before parent constructor.
		if (!$this->orderCol)
		{
			$this->orderCol = \JArrayHelper::getValue($config, 'order_column', null);
		}

		if (!$this->filterFields)
		{
			$this->filterFields = \JArrayHelper::getValue($config, 'filter_fields', null);
		}

		parent::__construct($config, $container, $state, $db);

		$this->container->registerServiceProvider(new FilterProvider($this->name));
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   12.2
	 */
	protected function _getListQuery()
	{
		// Capture the last store id used.
		static $lastStoreId;

		// Compute the current store id.
		$currentStoreId = $this->getStoreId();

		// If the last store id is different from the current, refresh the query.
		if ($lastStoreId != $currentStoreId || empty($this->query))
		{
			$lastStoreId = $currentStoreId;
			$this->query = $this->getListQuery();
		}

		return $this->query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		$items = $this->getList($query, $this->getStart(), $this->state->get('list.limit'));

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		$query = $this->db->getQuery(true);

		return $query;
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  \JPagination  A JPagination object for the data set.
	 *
	 * @since   12.2
	 */
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->state->get('list.limit') - (int) $this->state->get('list.links');
		$page  = new \JPagination($this->getTotal(), $this->getStart(), $limit);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   12.2
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->state->get('list.start');
		$id .= ':' . $this->state->get('list.limit');
		$id .= ':' . $this->state->get('list.ordering');
		$id .= ':' . $this->state->get('list.direction');
		$id .= ':' . serialize($this->state->get('filter', array()));
		$id .= ':' . serialize($this->state->get('search', array()));

		return md5($this->context . ':' . $id);
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getTotal()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the total.
		$query = $this->_getListQuery();

		$total = (int) $this->getListCount($query);

		// Add the total to the internal cache.
		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   12.2
	 */
	public function getStart()
	{
		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->state->get('list.start');
		$limit = $this->state->get('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|false  the JForm object or false
	 *
	 * @since   3.2
	 */
	public function getBatchForm($data = array(), $loadData = false)
	{
		return $this->loadForm($this->context . '.batch', 'batch', array('control' => '', 'load_data' => $loadData));
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|false  the JForm object or false
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		return $this->loadForm($this->context . '.filter', 'filter', array('control' => '', 'load_data' => $loadData));
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	3.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = $this->getContainer()->get('app');
		$data = $app->getUserState($this->context, new \stdClass);

		// Pre-fill the list options
		if (!property_exists($data, 'list'))
		{
			$data->list = array(
				'direction' => $this->state->get('list.direction'),
				'limit'     => $this->state->get('list.limit'),
				'ordering'  => $this->state->get('list.ordering'),
				'start'     => $this->state->get('list.start')
			);
		}

		$data->filter = $this->state->get('filter');

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// If the context is set, assume that stateful lists are used.
		if ($this->context)
		{
			$app = \JFactory::getApplication();

			// Receive & set filters
			if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
			{
				$filterValue = array();

				foreach ($filters as $name => $value)
				{
					if (in_array($name, $this->filterFields) && $value !== '')
					{
						$filterValue[$name] = $value;
					}
				}

				$this->state->set('filter', $filterValue);
			}

			// Receive & set searches
			if ($searches = $app->getUserStateFromRequest($this->context . '.search', 'search', array(), 'array'))
			{
				// Convert search field to array
				if (!empty($searches['field']) && !empty($searches['index']))
				{
					$searches[$searches['field']] = $searches['index'];
				}

				unset($searches['field']);
				unset($searches['index']);

				$searchValue = array();

				foreach ($searches as $name => $value)
				{
					if (in_array($name, $this->filterFields)  && $value)
					{
						$searchValue[$name] = $value;
					}
				}

				$this->state->set('search', $searchValue);
			}

			$limit = 0;

			// Receive & set list options
			if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
			{
				foreach ($list as $name => $value)
				{
					// Extra validations
					switch ($name)
					{
						case 'fullordering':
							$orderingParts = explode(',', $value);

							$ordering = array();

							foreach ($orderingParts as $i => $order)
							{
								$order = explode(' ', trim($order));

								if (count($order) == 2)
								{
									list($col, $dir) = $order;
								}
								else
								{
									$col = $order[0];
									$dir = '';
								}

								if (in_array($col, $this->filterFields))
								{
									$ordering[] = $dir ? $col . ' ' . strtoupper($dir) : $col;
								}
							}

							$last = array_pop($ordering);

							$last = explode(' ', $last);

							if (isset($last[1]) && in_array(strtoupper($last[1]), array('ASC', 'DESC')))
							{
								$this->state->set('list.direction', $last[1]);
							}

							$ordering[] = $last[0];

							$this->state->set('list.ordering', implode(', ', $ordering));
							break;

						case 'ordering':
							if (!in_array($value, $this->filterFields))
							{
								$value = $ordering;
							}
							break;

						case 'direction':
							if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
							{
								$value = $direction;
							}
							break;

						case 'limit':
							$limit = $value;
							break;

						// Just to keep the default case
						default:
							$value = $value;
							break;
					}

					$this->state->set('list.' . $name, $value);
				}
			}

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->state->set('list.start', $limitstart);
		}
		else
		{
			$this->state->set('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   \JForm   $form   A JForm object.
	 * @param   mixed    $data   The data expected for the form.
	 * @param   string   $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @throws  \Exception if there is an error in the form event.
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		\JPluginHelper::importPlugin($group);

		// Get the dispatcher.
		$dispatcher = $this->getContainer()->get('event.dispatcher');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof \Exception))
			{
				throw new \Exception($error);
			}
		}
	}

	/**
	 * processFilters
	 *
	 * @param \JDatabaseQuery $query
	 * @param array           $filters
	 *
	 * @return  \JDatabaseQuery
	 */
	protected function processFilters(\JDatabaseQuery $query, $filters = array())
	{
		$filters = $filters ? : $this->state->get('filter', array());

		$filterHelper = $this->container->get('model.' . strtolower($this->name) . '.filter');

		$this->configureFilters($filterHelper);

		$query = $filterHelper->execute($query, $filters);

		return $query;
	}

	/**
	 * configureFilters
	 *
	 * @param FilterHelper $filterHelper
	 *
	 * @return  void
	 */
	protected function configureFilters($filterHelper)
	{
		// Override this method.
	}

	/**
	 * processSearches
	 *
	 * @param \JDatabaseQuery $query
	 * @param array           $searches
	 *
	 * @return  \JDatabaseQuery
	 */
	protected function processSearches(\JDatabaseQuery $query, $searches = array())
	{
		$searches = $searches ? : $this->state->get('search', array());

		$searchHelper = $this->container->get('model.' . strtolower($this->name) . '.search');

		$this->configureSearches($searchHelper);

		$query = $searchHelper->execute($query, $searches);

		return $query;
	}

	/**
	 * configureSearches
	 *
	 * @param SearchHelper $searchHelper
	 *
	 * @return  void
	 */
	protected function configureSearches($searchHelper)
	{
		// Override this method.
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string   $key        The key of the user state variable.
	 * @param   string   $request    The name of the variable passed in a request.
	 * @param   string   $default    The default value for the variable if not found. Optional.
	 * @param   string   $type       Filter for the variable, for valid values see {@link \JFilterInput::clean()}. Optional.
	 * @param   boolean  $resetPage  If true, the limitstart in request is set to zero
	 *
	 * @return  array The request user state.
	 *
	 * @since   12.2
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app = \JFactory::getApplication();
		$input     = $app->input;
		$old_state = $app->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $input->get($request, null, $type);

		if (($cur_state != $new_state) && ($resetPage))
		{
			$input->set('limitstart', 0);
		}

		// Save the new value only if it is set in this request.
		if ($new_state !== null)
		{
			$app->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}
}
