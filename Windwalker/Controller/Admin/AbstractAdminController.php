<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

use Windwalker\Controller\Controller;

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
	 * Property viewItem.
	 *
	 * @var
	 */
	protected $viewItem;

	/**
	 * Property viewList.
	 *
	 * @var
	 */
	protected $viewList;

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
		// Guess the item view as the context.
		if (empty($this->viewItem))
		{
			$this->view_item = $this->context;
		}

		parent::__construct($input, $app);

		$this->user    = \JFactory::getUser();
		$this->context = $this->input->get('task', $this->input->get('controller'));
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
}
