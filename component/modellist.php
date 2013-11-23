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

jimport('joomla.application.component.modellist');

/**
 * Model class for handling lists of items.
 *
 * @package     Windwalker.Framework
 * @subpackage  Component
 */
class AKModelList extends JModelList
{
	/**
	 * Component name.
	 *
	 * @var string
	 */
	protected $component = '';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 */
	protected $item_name = '';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 */
	protected $list_name = '';

	/**
	 * Items cache.
	 *
	 * @var object
	 */
	protected $items = null;

	/**
	 * Category cache.
	 *
	 * @var object
	 */
	protected $category = null;

	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see      JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type    $type    The table type to instantiate
	 * @param    string  $prefix  A prefix for the table class name. Optional.
	 * @param    array   $config  Configuration array for model. Optional.
	 *
	 * @return   JTable  A database object
	 */
	public function getTable($type = null, $prefix = null, $config = array())
	{
		$prefix = $prefix ? $prefix : ucfirst($this->component) . 'Table';
		$type   = $type ? $type : $this->item_name;

		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the parameters.
		$params = JComponentHelper::getParams($this->option);
		$this->setState('params', $params);

		// Fulltext search
		if (isset($this->config['fulltext_search']))
		{
			$this->setState('search.fulltext', $this->config['fulltext_search']);
		}

		// Core sidebar
		if (isset($this->config['core_sidebar']))
		{
			$this->setState('core_sidebar', $this->config['core_sidebar']);
		}

		// Set Nested Items
		// ========================================================================
		$table = $this->getTable();

		if ($table instanceof JTableNested)
		{
			$nested = true;
		}
		else
		{
			$nested = false;
		}
		$this->setState('items.nested', $nested);

		// Set all filter fields
		// ========================================================================
		$filter        = $app->getUserStateFromRequest($this->context . '.field.filter', 'filter');
		$filter_fields = array();

		foreach ($this->filter_fields as $field)
		{
			$filter_fields[$field] = JArrayHelper::getValue($filter, $field, '');
		}

		$this->setState('filter', $filter_fields);

		// Set all search fields
		// ========================================================================
		$search       = $app->getUserStateFromRequest($this->context . '.field.search', 'search');
		$allow_search = array();

		if (in_array(JArrayHelper::getValue($search, 'field'), $this->filter_fields) || $this->config['fulltext_search'])
		{
			$allow_search['field'] = JArrayHelper::getValue($search, 'field');
			$allow_search['index'] = JArrayHelper::getValue($search, 'index');
		}

		foreach ($this->filter_fields as $field)
		{
			$allow_search[$field] = JArrayHelper::getValue($search, $field, '');
		}

		$this->setState('search', $allow_search);

		// List state information.
		if (!$ordering)
		{
			$ordering = $nested ? 'a.lft' : 'a.ordering';
		}

		$orderCol = $nested ? 'a.lft' : 'a.ordering';
		$this->setState('list.orderCol', $orderCol);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param    string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . json_encode($this->getState('search'));
		$id .= ':' . json_encode($this->getState('filter'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to get list page filter form.
	 *
	 * @return    object  JForm object.
	 */
	public function getFilter()
	{
		if (!empty($this->filter))
		{
			return $this->filter;
		}

		// Get filter inputs from from xml files in /models/form.
		JForm::addFormPath(AKHelper::_('path.get', null, $this->option) . '/models/forms');
		JForm::addFormPath(AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->list_name);
		JForm::addFieldPath(AKHelper::_('path.get', null, $this->option) . '/models/fields');

		// Generate sidebar filter by Joomla! core system.
		if (JVERSION >= 3 && $this->config['core_sidebar'])
		{

			// Get filter inputs from raw xml file.
			$file = AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->list_name . '_filter.xml';
			$file = JFile::exists($file) ? $file : AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->list_name . '/filter.xml';
			$xml  = simplexml_load_file($file);

			$filters = $xml->xpath('//fieldset[@name="filter_sidebar"]');
			$filters = $filters[0]->field;

			$form['filter_sidebar'] = $filters;
		}

		// Load forms
		$form_path = AKHelper::_('path.get', null, $this->option) . '/models/forms/';

		// Search
		if (JFile::exists($form_path . $this->list_name . '/search.xml'))
		{
			$form['search'] = JForm::getInstance("{$this->option}.{$this->list_name}.search", 'search', array('control' => 'search', 'load_data' => 'true'));
		}
		else
		{
			// Legacy
			$form['search'] = JForm::getInstance(
				"{$this->option}.{$this->list_name}.search",
				$this->list_name . '_search',
				array(
					'control'   => 'search',
					'load_data' => 'true'
				)
			);
		}

		// Filter
		if (JFile::exists($form_path . $this->list_name . '/filter.xml'))
		{
			$form['filter'] = JForm::getInstance("{$this->option}.{$this->list_name}.filter", 'filter', array('control' => 'filter', 'load_data' => 'true'));
		}
		else
		{
			// Legacy
			$form['filter'] = JForm::getInstance(
				"{$this->option}.{$this->list_name}.filter",
				$this->list_name . '_filter',
				array(
					'control'   => 'filter',
					'load_data' => 'true'
				)
			);
		}

		// Batch
		if (JFile::exists($form_path . $this->list_name . '/batch.xml'))
		{
			$form['batch'] = JForm::getInstance("{$this->option}.{$this->list_name}.batch", 'batch', array('control' => 'batch', 'load_data' => 'true'));
		}

		// Get default data of this form. Any State key same as form key will auto match.
		$form['search']->bind($this->getState('search'));
		$form['filter']->bind($this->getState('filter'));

		return $this->filter = $form;
	}

	/**
	 * Method to get category by catid.
	 *
	 * @param   integer $pk Category id.
	 *
	 * @return  mixed   Category object or false.
	 */
	public function getCategory()
	{
		if (!empty($this->category))
		{
			return $this->category;
		}

		$pk = $this->getState('category.id');

		$this->category = JTable::getInstance('Category');
		$this->category->load($pk);

		return $this->category;
	}

	/**
	 * Get fields from search XML file.
	 *
	 * @return  array    fields name.
	 */
	public function getFullSearchFields()
	{
		$file = AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->list_name . '/search.xml';
		$file = JFile::exists($file) ? $file : AKHelper::_('path.get', null, $this->option) . '/models/forms/' . $this->list_name . '_search.xml';

		$xml     = simplexml_load_file($file);
		$field   = $xml->xpath('//field[@name="field"]');
		$options = $field[0]->option;

		$fields = array();

		foreach ($options as $option):
			$attr     = $option->attributes();
			$fields[] = $attr['value'];
		endforeach;

		return $fields;
	}

	/**
	 * Set search condition to support multiple search inputs.
	 *
	 * @param   array           $search  Search fields and values.
	 * @param   JDatabaseQuery  $q       The query object.
	 * @param   array           $ignore  An array for ignore fields.
	 *
	 * @return  JDatabaseQuery
	 */
	public function searchCondition($search, $q = null, $ignore = array())
	{
		$db = JFactory::getDbo();

		if (!$q)
		{
			$q = $db->getQuery();
		}

		$search_where = array();

		// One Search Input
		// ========================================================================
		if (JArrayHelper::getValue($search, 'index'))
		{
			// Fulltext Search
			if ($this->getState('search.fulltext') || $search['field'] == '*')
			{
				$fields = $this->getFullSearchFields();
				array_shift($fields);

				foreach ($fields as &$field):
					$field = (string) $field;

					// Ignore fields
					if (in_array($field, $ignore))
					{
						continue;
					}

					$field = $db->qn($field);
					$field = "{$field} LIKE '%{$search['index']}%'";
				endforeach;

				if (count($fields))
				{
					$search_where[] = "( " . implode(' OR ', $fields) . " )";
				}
			}
			else
			{
				// Serach one field
				if (!in_array($search['field'], $ignore))
				{
					$search_where[] = "{$db->qn($search['field'])} LIKE '%{$search['index']}%'";
				}
			}
		}

		// Multiple Search Input
		// ========================================================================

		// Remove One search input first
		unset($search['index']);
		unset($search['field']);
		$condition = array();

		foreach ((array) $search as $key => $val)
		{
			// Ignore fields
			if (in_array($key, $ignore))
			{
				continue;
			}

			if ($val)
			{
				$condition[] = "{$db->qn($key)} LIKE '%{$val}%'";
			}
		}

		if (count($condition))
		{
			$search_where[] = "( " . implode(' OR ', $condition) . " )";
		}

		// Build All Query
		// ========================================================================
		if (count($search_where))
		{
			$q->where(implode(' OR ', $search_where));
		}

		return $q;
	}

	/**
	 * Set query filter.
	 *
	 * @param   array           $filter  Filter fields and values.
	 * @param   JDatabaseQuery  $q       The query object.
	 * @param   array           $ignore  An array for ignore fields.
	 *
	 * @return  type
	 */
	public function filterCondition($filter, $q = null, $ignore = array())
	{
		$db = JFactory::getDbo();

		if (!$q)
		{
			$q = $db->getQuery();
		}

		// Start Filter
		// ========================================================================
		foreach ($filter as $k => $v)
		{
			// If this field in ignore, jump.
			if (in_array($k, $ignore))
			{
				continue;
			}

			// Filter Condition
			if ($v !== '' && $v != '*')
			{
				$k = $db->qn($k);
				$q->where("{$k}='{$v}'");
			}
		}

		return $q;
	}
}
