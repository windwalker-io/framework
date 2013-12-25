<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

/**
 * Class FormController
 *
 * @since 1.0
 */
abstract class AbstractItemController extends AbstractAdminController
{
	/**
	 * Property key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Property urlVar.
	 *
	 * @var string
	 */
	protected $urlVar;

	/**
	 * Property recordId.
	 *
	 * @var
	 */
	protected $recordId;

	/**
	 * Property data.
	 *
	 * @var
	 */
	protected $data;

	/**
	 * Property table.
	 *
	 * @var
	 */
	protected $table;

	/**
	 * Property model.
	 *
	 * @var
	 */
	protected $model;

	/**
	 * Instantiate the controller.
	 *
	 * @param   \JInput           $input  The input object.
	 * @param   \JApplicationCms  $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(\JInput $input = null, \JApplicationCms $app = null, $config = array())
	{
		parent::__construct($input, $app);
	}

	/**
	 * prepare
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		$this->lang     = \JFactory::getLanguage();
		$this->model    = $this->getModel();
		$this->table    = $this->model->getTable();
		$this->data     = $this->input->post->get('jform', array(), 'array');
		$this->context  = "$this->option.edit.$this->context";

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$this->key = $this->table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$this->urlVar = $this->key;
		}

		$this->recordId = $this->input->getInt($this->urlVar);

		// Populate the row id from the session.
		$this->data[$this->key] = $this->recordId;
	}


	/**
	 * Method to add a record ID to the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function holdEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Add the id to the list if non-zero.
		if (!empty($id))
		{
			array_push($values, (int) $id);
			$values = array_unique($values);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				\JLog::add(
					sprintf(
						'Holding edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					\JLog::INFO,
					'controller'
				);
			}
		}
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function releaseEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Do a strict search of the edit list values.
		$index = array_search((int) $id, $values, true);

		if (is_int($index))
		{
			unset($values[$index]);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				\JLog::add(
					sprintf(
						'Releasing edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					\JLog::INFO,
					'controller'
				);
			}
		}
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   12.2
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			$values = (array) $this->app->getUserState($context . '.id');

			$result = in_array((int) $id, $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				\JLog::add(
					sprintf(
						'Checking edit ID %s.%s: %d %s',
						$context,
						$id,
						(int) $result,
						str_replace("\n", ' ', print_r($values, 1))
					),
					\JLog::INFO,
					'controller'
				);
			}

			return $result;
		}
		else
		{
			// No id for a new item.
			return true;
		}
	}
}
