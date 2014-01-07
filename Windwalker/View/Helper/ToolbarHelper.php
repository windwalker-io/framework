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
use Windwalker\DI\Container;
use Windwalker\Helper\ArrayHelper;
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
	 * @var  array  Property buttonSet.
	 */
	protected $buttonSet = array();

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
	 * __call
	 *
	 * @param $name
	 * @param $args
	 *
	 * @return  void
	 */
	public function register($button, $value)
	{
		if (!$this->checkAccess($button, $value))
		{
			return;
		}

		$dispatcher = Container::getInstance()->get('event.dispatcher');

		$args = func_get_args();

		array_shift($args);
		array_shift($args);

		$callback = '';

		if (is_string($value) || is_callable($value))
		{
			$value = array('handler' => $value);
		}

		if (is_array($value) && !empty($value['handler']))
		{
			$callback = array($this, $value['handler']);

			if (!is_callable($callback))
			{
				$callback = array('JToolbarHelper', $value['handler']);
			}

			if (!empty($value['args']))
			{
				$args = (array) $value['args'];
			}
		}

		if (is_callable($value['handler']))
		{
			$callback = $value['handler'];
		}

		if (is_callable($callback))
		{
			$dispatcher->trigger('onToolbarAppendButton', array($button, &$args));

			call_user_func_array($callback, $args);
		}
		else
		{
			$app = \JFactory::getApplication();
			$app->enqueueMessage(sprintf('%s not found', $button));
		}
	}

	/**
	 * checkAccess
	 *
	 * @param $name
	 * @param $button
	 *
	 * @return  bool|mixed
	 */
	protected function checkAccess($name, $button)
	{
		if (!isset($button['access']))
		{
			return true;
		}

		elseif (is_string($button['access']))
		{
			return $this->access->get($button['access']);
		}

		return $button['access'];
	}

	public function registerButtons()
	{
		$buttons = $this->buttonSet;

		$queue = new \SplPriorityQueue;

		foreach ($buttons as $name => $button)
		{
			$priority = isset($priority) ? ArrayHelper::getValue($button, 'priority', $priority + 10) : 9999;

			$queue->insert($name, $priority);
		}

		foreach ($queue as $name)
		{
			$this->register($name, $buttons[$name]);
		}
	}

	/**
	 * duplicate
	 *
	 * @param string $task
	 * @param string $alt
	 *
	 * @return  void
	 */
	public function duplicate($task = 'default.batch.copy', $alt = 'JTOOLBAR_DUPLICATE')
	{
		JToolBarHelper::custom($task, 'copy', 'copy_f2', 'JTOOLBAR_DUPLICATE', true);
	}

	/**
	 * Displays a modal button
	 *
	 * @param   string  $targetModalId  ID of the target modal box
	 * @param   string  $icon           Icon class to show on modal button
	 * @param   string  $alt            Title for the modal button
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function modal($targetModalId = 'batchModal', $icon = 'icon-checkbox-partial', $alt = 'JTOOLBAR_BATCH')
	{
		JToolbarHelper::modal($targetModalId, $icon, $alt);
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string   $component  The name of the component, eg, com_content.
	 * @param   string   $alt        The name of the button.
	 * @param   string   $path       An alternative path for the configuation xml relative to JPATH_SITE.
	 *
	 * @return  void
	 */
	public function preferences($component = null, $alt = 'JToolbar_Options', $path = '')
	{
		$component = $component ? : $this->config->get('option', \JFactory::getApplication()->input->get('option'));
		$component = urlencode($component);

		JToolbarHelper::preferences($component, $alt, $path);
	}
}
