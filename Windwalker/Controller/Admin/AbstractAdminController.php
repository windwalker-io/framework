<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

use Windwalker\Model\Model;
use Windwalker\Table\Table;

/**
 * Class AdminController
 *
 * @since 1.0
 */
abstract class AbstractAdminController extends AbstractRedirectController
{
	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context;

	/**
	 * Property user.
	 *
	 * @var \JUser
	 */
	protected $user = null;

	/**
	 * Property textPrefix.
	 *
	 * @var string
	 */
	protected $textPrefix = null;

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
	 * Property table.
	 *
	 * @var Table
	 */
	protected $table;

	/**
	 * Property model.
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Property lang.
	 *
	 * @var \JLanguage
	 */
	protected $lang;

	/**
	 * Instantiate the controller.
	 *
	 * @param   \JInput          $input  The input object.
	 * @param   \JApplicationCms $app    The application object.
	 * @param   array            $config Additional config.
	 *
	 * @throws \Exception
	 * @since  12.1
	 */
	public function __construct(\JInput $input = null, \JApplicationCms $app = null, $config = array())
	{
		parent::__construct($input, $app, $config);

		$this->user       = \JFactory::getUser();
		$this->context    = $this->option . '.' . $this->task;
		$this->textPrefix = strtoupper($this->option);
	}

	/**
	 * prepareExecute
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		$this->lang  = \JFactory::getLanguage();
		$this->model = $this->getModel();
		$this->table = $this->model->getTable();

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
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		return (
			$this->user->authorise('core.create', $this->option)
			|| count($this->user->getAuthorisedCategories($this->option, 'core.create'))
		);
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : '0';

		if ($recordId)
		{
			return $this->allowEdit($data, $key);
		}
		else
		{
			return $this->allowAdd($data);
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return $this->user->authorise('core.edit', $this->option);
	}

	/**
	 * allowEditState
	 *
	 * @param array  $data
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function allowUpdateState($data = array(), $key = 'id')
	{
		return $this->user->authorise('core.edit.state', $this->option);
	}

	/**
	 * allowDelete
	 *
	 * @param array  $data
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function allowDelete($data = array(), $key = 'id')
	{
		return $this->user->authorise('core.edit', $this->option);
	}
}
