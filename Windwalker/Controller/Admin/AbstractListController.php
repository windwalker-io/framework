<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

use Windwalker\Table\Table;

/**
 * Class AbstractAdminController
 *
 * @since 1.0
 */
abstract class AbstractListController extends AbstractAdminController
{
	/**
	 * Property cid.
	 *
	 * @var int[]
	 */
	protected $cid;

	/**
	 * Instantiate the controller.
	 *
	 * @param \JInput          $input   The input object.
	 * @param \JApplicationCms $app     The application object.
	 * @param array            $config  Config.
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

		$this->cid     = $this->input->get('cid', array(), 'array');
		$this->context = $this->option . '.list.' . $this->context;
	}

	/**
	 * getModel
	 *
	 * @param null  $name
	 * @param null  $prefix
	 * @param array $config
	 * @param bool  $forceNew
	 *
	 * @return mixed
	 */
	public function getModel($name = null, $prefix = null, $config = array(), $forceNew = false)
	{
		if (!$name)
		{
			$name = $this->viewItem;
		}

		return parent::getModel($name, $prefix, $config);
	}
}
