<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use JFactory;
use JFilterOutput;
use Joomla\DI\Container as JoomlaContainer;
use JTable;

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
	 * Property reorderConditions.
	 *
	 * @var array
	 */
	protected $reorderConditions = array();

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

		if (!$this->reorderConditions)
		{
			$this->reorderConditions = \JArrayHelper::getValue($config, 'reorder_conditions', array('catid'));
		}
	}

	/**
	 * save
	 *
	 * @param array $data
	 *
	 * @return  bool
	 */
	public function save($data)
	{
		$result = parent::save($data);

		if ($result && $this->state->get('order.position') == 'first')
		{
			// Do reorder
			$pk = $this->state->get($this->getName() . '.id');

			$this->reorder(array($pk), array(0));

			$this->state->set('order.position', null);
		}

		return $result;
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
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function reorder($pks = null, $order = null)
	{
		$table          = $this->getTable();
		$tableClassName = get_class($table);
		$contentType    = new \JUcmType;
		$type           = $contentType->getTypeByTable($tableClassName);
		$typeAlias      = $type ? $type->type_alias : null;
		$tagsObserver   = $table->getObserverOfClass('JTableObserverTags');
		$conditions     = array();
		$errors         = array();
		$orderCol       = $this->state->get('reorder.column', 'ordering');

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load($pk);

			$table->$orderCol = $order[$i];

			$this->createTagsHelper($tagsObserver, $type, $pk, $typeAlias, $table);

			if (!$table->store())
			{
				$errors[] = $table->getError();

				continue;
			}

			// Remember to reorder within position and client_id
			$condition = $this->getReorderConditions($table);
			$found     = false;

			// Found reorder condition if is cached.
			foreach ($conditions as $cond)
			{
				if ($cond['cond'] == $condition)
				{
					$found = true;
					break;
				}
			}

			// If not found, we add this condition to cache.
			if (!$found)
			{
				$key = $table->getKeyName();

				$conditions[] = array(
					'pk'   => $table->$key,
					'cond' => $condition
				);
			}
		}

		// Execute all reorder for each condition caches.
		foreach ($conditions as $cond)
		{
			$table->load($cond['pk']);
			$table->reorder($cond['cond']);
		}

		$this->state->set('error.message',  $errors);

		// Clear the component's cache
		$this->cleanCache();

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
		$date = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
		$user = $this->container->get('user');

		// Alias
		if (property_exists($table, 'alias'))
		{
			if (!$table->alias)
			{
				$table->alias = JFilterOutput::stringURLSafe(trim($table->title));
			}
			else
			{
				$table->alias = JFilterOutput::stringURLSafe(trim($table->alias));
			}

			if (!$table->alias)
			{
				$table->alias = JFilterOutput::stringURLSafe($date->toSql(true));
			}
		}

		// Created date
		if (property_exists($table, 'created') && !$table->created)
		{
			$table->created = $date->toSql(true);
		}

		// Publish_up date
		if (property_exists($table, 'publish_up') && !$table->publish_up)
		{
			$table->publish_up = $date->toSql(true);
		}

		// Modified date
		if (property_exists($table, 'modified') && $table->id)
		{
			$table->modified = $date->toSql(true);
		}

		// Created user
		if (property_exists($table, 'created_by') && !$table->created_by)
		{
			$table->created_by = $user->get('id');
		}

		// Modified user
		if (property_exists($table, 'modified_by') && $table->id)
		{
			$table->modified_by = $user->get('id');
		}

		// Set Ordering or Nested ordering
		if (property_exists($table, 'ordering'))
		{
			if (empty($table->id))
			{
				$this->setOrderPosition($table);
			}
		}
	}

	/**
	 * Method to set new item ordering as first or last.
	 *
	 * @param   JTable $table    Item table to save.
	 * @param   string $position 'first' or other are last.
	 *
	 * @return  void
	 */
	public function setOrderPosition($table, $position = null)
	{
		if ($position == 'first')
		{
			if (!$table->ordering)
			{
				$table->ordering = 1;

				$this->state->set('order.position', 'first');
			}
		}
		else
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$query = $this->db->getQuery(true)
					->select('MAX(ordering)')
					->from($table->getTableName());

				$condition = $this->getReorderConditions($table);

				// Condition should be an array.
				if (count($condition))
				{
					$query->where($this->getReorderConditions($table));
				}

				$max = $this->db->setQuery($query)->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   12.2
	 */
	protected function getReorderConditions($table)
	{
		$fields = $this->state->get('reorder.condition.fields', $this->reorderConditions);

		$condition = array();

		foreach ($fields as $field)
		{
			if (property_exists($table, $field))
			{
				$condition[] = $this->db->quoteName($field) . '=' . $this->db->quote($table->$field);
			}
		}

		return $condition;
	}

	/**
	 * Method to create a tags helper to ensure proper management of tags
	 *
	 * @param   \JTableObserverTags  $tagsObserver  The tags observer for this table
	 * @param   \JUcmType            $type          The type for the table being processed
	 * @param   integer              $pk            Primary key of the item bing processed
	 * @param   string               $typeAlias     The type alias for this table
	 * @param   \JTable              $table         The JTable object
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function createTagsHelper($tagsObserver, $type, $pk, $typeAlias, $table)
	{
		if (!empty($tagsObserver) && !empty($type))
		{
			$table->tagsHelper = new \JHelperTags;
			$table->tagsHelper->typeAlias = $typeAlias;
			$table->tagsHelper->tags = explode(',', $table->tagsHelper->getTagIds($pk, $typeAlias));
		}
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	12.2
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			$title = \JString::increment($title);
			$alias = \JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}
}
