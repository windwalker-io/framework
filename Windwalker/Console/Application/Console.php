<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Application;

use Windwalker\Bundle\Generator\GeneratorBundle;
use Windwalker\Console\Descriptor\OptionDescriptor;
use Joomla\Application\Cli\CliOutput;
use Joomla\Console\Console as JoomlaConsole;
use Joomla\Application\Cli\Output;
use Joomla\Input;
use Joomla\Registry\Registry;

/**
 * Class Console
 *
 * @since  3.2
 */
class Console extends JoomlaConsole
{
	/**
	 * The application dispatcher object.
	 *
	 * @var    \JEventDispatcher
	 * @since  3.2
	 */
	protected $dispatcher;

	/**
	 * The Console title.
	 *
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $name = 'Windwalker Console';

	/**
	 * Version of this application.
	 *
	 * @var string
	 *
	 * @since  1.0
	 */
	protected $version = '2.0';

	/**
	 * Class constructor.
	 *
	 * @param   Input\Cli  $input   An optional argument to provide dependency injection for the application's
	 *                              input object.  If the argument is a InputCli object that object will become
	 *                              the application's input object, otherwise a default input object is created.
	 *
	 * @param   Registry   $config  An optional argument to provide dependency injection for the application's
	 *                              config object.  If the argument is a Registry object that object will become
	 *                              the application's config object, otherwise a default config object is created.
	 *
	 * @param   CliOutput  $output  The output handler.
	 *
	 * @since   1.0
	 */
	public function __construct(Input\Cli $input = null, Registry $config = null, CliOutput $output = null)
	{
		$this->loadDispatcher();

		\JFactory::$application = $this;

		parent::__construct($input, $config, $output);

		$descriptorHelper = $this->defaultCommand->getArgument('help')
			->getDescriptor();

		$descriptorHelper->setOptionDescriptor(new OptionDescriptor);

		$this->loadFirstlevelCommands();
	}

	/**
	 * loadFirstlevelCommands
	 *
	 * @return void
	 */
	protected function loadFirstlevelCommands()
	{
		try
		{
			\JPluginHelper::importPlugin('windwalker');
		}
		catch (\RuntimeException $e)
		{
			// Do nothing
		}

		GeneratorBundle::registerCommands($this);

		$context = get_class($this->defaultCommand);

		$this->triggerEvent('onWindwalkerLoadCommand', array($context, $this->defaultCommand));
	}

	/**
	 * Allows the application to load a custom or default dispatcher.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create event
	 * dispatchers, if required, based on more specific needs.
	 *
	 * @param   \JEventDispatcher  $dispatcher  An optional dispatcher object. If omitted, the factory dispatcher is created.
	 *
	 * @return  Console This method is chainable.
	 *
	 * @since   12.1
	 */
	public function loadDispatcher(\JEventDispatcher $dispatcher = null)
	{
		$this->dispatcher = ($dispatcher === null) ? \JEventDispatcher::getInstance() : $dispatcher;

		return $this;
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since   12.1
	 */
	public function triggerEvent($event, array $args = null)
	{
		if ($this->dispatcher instanceof \JEventDispatcher)
		{
			return $this->dispatcher->trigger($event, $args);
		}

		return null;
	}
}
