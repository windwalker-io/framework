<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Application;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\IO\Input;
use Windwalker\Registry\Registry;

/**
 * Class AbstractApplication
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractApplication implements LoggerAwareInterface
{
	/**
	 * The application configuration object.
	 *
	 * @var    Registry
	 * @since  {DEPLOY_VERSION}
	 */
	protected $config;

	/**
	 * The application input object.
	 *
	 * @var    Input
	 * @since  {DEPLOY_VERSION}
	 */
	public $input = null;

	/**
	 * A logger.
	 *
	 * @var    LoggerInterface
	 * @since  {DEPLOY_VERSION}
	 */
	protected $logger;

	/**
	 * Class constructor.
	 *
	 * @param   Input     $input   An optional argument to provide dependency injection for the application's
	 *                             input object.  If the argument is a InputCli object that object will become
	 *                             the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's
	 *                             config object.  If the argument is a Registry object that object will become
	 *                             the application's config object, otherwise a default config object is created.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct(Input $input = null, Registry $config = null)
	{
		$this->input = $input instanceof Input ? $input : new Input;
		$this->config = $config instanceof Registry ? $config : new Registry;

		$this->initialise();
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer|string  $message  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function close($message = 0)
	{
		exit($message);
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	abstract protected function doExecute();

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function execute()
	{
		$this->prepareExecute();

		// @event onBeforeExecute

		// Perform application routines.
		$this->doExecute();

		// @event onAfterExecute

		$this->postExecute();
	}

	/**
	 * Prepare execute hook.
	 *
	 * @return  void
	 */
	protected function prepareExecute()
	{
	}

	/**
	 * Pose execute hook.
	 *
	 * @return  mixed
	 */
	protected function postExecute()
	{
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Get the logger.
	 *
	 * @return  LoggerInterface
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getLogger()
	{
		// If a logger hasn't been set, use NullLogger
		if (! ($this->logger instanceof LoggerInterface))
		{
			$this->logger = new NullLogger;
		}

		return $this->logger;
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the AbstractApplication::__construct method.
	 * This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function initialise()
	{
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);

		return $previous;
	}

	/**
	 * Sets the configuration for the application.
	 *
	 * @param   Registry  $config  A registry object holding the configuration.
	 *
	 * @return  AbstractApplication  Returns itself to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setConfiguration(Registry $config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Set the logger.
	 *
	 * @param   LoggerInterface  $logger  The logger.
	 *
	 * @return  AbstractApplication  Returns itself to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}
}

