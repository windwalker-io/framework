<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller;

use GeneratorBundle\Action\Action;
use Joomla\Registry\Registry;
use Windwalker\Console\Command\Command;
use Windwalker\Console\Controller\Controller;

/**
 * Class TaskController
 *
 * @since 1.0
 */
abstract class TaskController extends Controller
{
	/**
	 * Property config.
	 *
	 * @var Registry
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param   Command   $command
	 * @param   Registry  $config
	 */
	public function __construct(Command $command, $config = null)
	{
		$this->config = new Registry($config);

		parent::__construct($command);
	}

	/**
	 * doAction
	 *
	 * @param Action $action
	 *
	 * @return  $this
	 */
	public function doAction(Action $action)
	{
		$action->execute($this);

		return $this;
	}

	/**
	 * get
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return  mixed
	 */
	public function get($key, $default)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * set
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return  TaskController
	 */
	public function set($key, $value)
	{
		$this->config->set($key, $value);

		return $this;
	}

	/**
	 * getConfig
	 *
	 * @return  Registry
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * setConfig
	 *
	 * @param   Registry $config
	 *
	 * @return  TaskController  Return self to support chaining.
	 */
	public function setConfig($config)
	{
		$this->config = $config;

		return $this;
	}
}
