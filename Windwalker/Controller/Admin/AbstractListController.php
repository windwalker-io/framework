<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

/**
 * Class AbstractAdminController
 *
 * @since 1.0
 */
abstract class AbstractListController extends AbstractAdminController
{
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
	 * Property lang.
	 *
	 * @var
	 */
	protected $lang;

	/**
	 * Property cid.
	 *
	 * @var
	 */
	protected $cid;

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
		parent::__construct($input, $app, $config);

		// Guess the item view as the context.
		if (empty($this->viewList))
		{
			$this->viewList = $this->getName();
		}

		// Guess the list view as the plural of the item view.
		if (empty($this->viewItem))
		{
			$inflector = \JStringInflector::getInstance();

			$this->viewItem = $inflector->toSingular($this->viewList);
		}
	}

	/**
	 * prepareExecute
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();

		$this->lang     = \JFactory::getLanguage();
		$this->model    = $this->getModel($this->viewItem);
		$this->table    = $this->model->getTable($this->viewItem);
		$this->cid      = $this->input->post->get('cid', array(), 'array');
		$this->context  = $this->option . '.state.' . $this->context;
	}
}
