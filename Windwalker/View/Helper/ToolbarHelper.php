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
	 * @var    Property buttonCallable.
	 */
	protected $buttonCallable;
	/**
	 * @var  array  Property buttonSet.
	 */
	private $buttonSet;

	/**
	 * Constructor.
	 *
	 * @param object $view
	 * @param array  $config
	 */
	public function __construct($data, array $buttonSet = array(), $config = array())
	{
		$this->data      = $data;
		$this->config    = $config ? : new Registry($config);
		$this->state     = $state = $data->state;
		$this->buttonSet = $buttonSet;

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
	public function register($button, $value)
	{
		$args = func_get_args();

		array_shift($args);
		array_shift($args);

		$callback = '';

		if (is_string($value))
		{
			$callback = array($this, $value);

			if (!is_callable($callback))
			{
				$callback = array('JToolbarHelper', $value);
			}
		}

		elseif (is_array($value) && !empty($value['code']))
		{
			$callback = $value['code'];

			if (!empty($value['arguments']))
			{
				$args = (array) $value['arguments'];
			}
		}

		if (is_callable($value))
		{
			$callback = $value;
		}

		if (!is_callable($callback))
		{
			$callback = $this->buttonCallable[$button];
		}

		if (is_callable($callback))
		{
			call_user_func_array($callback, $args);
		}
		else
		{
			$app = \JFactory::getApplication();
			$app->enqueueMessage(sprintf('%s not found', $button));
		}
	}

	protected function checkAccess($name, $args)
	{

		return true;
	}

	public function registerButtons()
	{
		$buttons = $this->buttonSet;

		foreach ($buttons as $button => $type)
		{
			$this->register($button, $type);
		}
	}
}
