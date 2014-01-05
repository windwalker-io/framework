<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Helper;

use JToolbar;
use JToolbarHelper;
use Windwalker\Data\Data;
use Windwalker\Object\Object;
use Windwalker\Registry\Registry;

/**
 * Class ToolbarHelper
 *
 * @since 1.0
 */
class ToolbarHelper
{
	/**
	 * @var  Data  Property data.
	 */
	protected $data;

	/**
	 * @var  Registry  Property config.
	 */
	protected $config;

	/**
	 * @var  Object  Property access.
	 */
	protected $access;

	/**
	 * Constructor.
	 *
	 * @param object $view
	 * @param array  $config
	 */
	public function __construct($data, $config = array())
	{
		$this->data   = $data;
		$this->config = $config ? : new Registry($config);
		$this->state  = $state = $data->state;

		// Access
		$access = (array) $this->config->get('access');

		$this->access = new Object($access);
	}

	/**
	 * addNew
	 *
	 * @return  void
	 */
	public function addNew($task = null, $alt = 'JTOOLBAR_NEW', $check = false)
	{
		$task = $task ?: $this->config->get('view_item') . '.edit.add';

		if ($this->access->get('core.create'))
		{
			JToolBarHelper::addNew($task, $alt, $check);
		}
	}

	/**
	 * __call
	 *
	 * @param $name
	 * @param $args
	 *
	 * @return  void
	 */
	public function register($name)
	{
		$args = func_get_args();

		array_shift($args);

		if (!$this->checkAccess($name, $args))
		{
			return;
		}

		$callback = array('JToolbarHelper', $name);

		if (is_callable($callback))
		{
			call_user_func_array($callback, $args);
		}
	}

	protected function checkAccess($name, $args)
	{

		return true;
	}

	public function registerButtons()
	{
		$buttons = $this->config->get('buttons');

		foreach ($buttons as $button)
		{
			$this->register($button);
		}
	}
}
