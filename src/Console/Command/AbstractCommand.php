<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Command;

use Windwalker\Console\Console;
use Windwalker\Console\Exception\CommandNotFoundException;
use Windwalker\Console\Option\Option;
use Windwalker\Console\Option\OptionSet;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;

/**
 * Abstract Console class.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractCommand implements \ArrayAccess
{
	/**
	 * Console application.
	 *
	 * @var  Console
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public $application;

	/**
	 * The Cli input object.
	 *
	 * @var IOInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $io;

	/**
	 * Console(Argument) name.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $name;

	/**
	 * The children(SubCommends) storage.
	 *
	 * @var AbstractCommand[]
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $children = array();

	/**
	 * The Options storage.
	 *
	 * @var OptionSet
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $options = null;

	/**
	 * Global Options.
	 *
	 * @var OptionSet
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $globalOptions = null;

	/**
	 * The command description.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $description;

	/**
	 * The manual about this command.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $help;

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $usage = '%s <cmd><command></cmd> <option>[option]</option>';

	/**
	 * The closure to execute.
	 *
	 * @var  callable
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $handler;

	/**
	 * The parent Console if this is a sub comment.
	 *
	 * @var AbstractCommand
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $parent;

	/**
	 * Console constructor.
	 *
	 * @param   string           $name    Console name.
	 * @param   IOInterface      $io      Cli input object.
	 * @param   AbstractCommand  $parent  Parent Console.
	 *
	 * @throws \LogicException
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function __construct($name = null, IOInterface $io = null, AbstractCommand $parent = null)
	{
		$this->name   = $name ? : $this->name;
		$this->io     = $io ? : new IO;
		$this->parent = $parent;

		$this->options       = new OptionSet;
		$this->globalOptions = new OptionSet;

		$this->configure();

		if (!$this->name)
		{
			throw new \LogicException('Console name can not be empty.');
		}
	}

	/**
	 * Execute this command.
	 *
	 * @return  mixed  Executed result or exit code.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function execute()
	{
		if (count($this->children) && count($this->io->getArguments()))
		{
			$name = $this->io->getArgument(0);

			try
			{
				return $this->executeSubCommand($name);
			}
			catch (CommandNotFoundException $e)
			{
				$e->getCommand()->renderAlternatives($e->getChild(), $e);

				return $e->getCode();
			}
			catch (\Exception $e)
			{
				$this->renderException($e);

				return $e->getCode();
			}
		}

		if ($this->handler)
		{
			if ($this->handler instanceof \Closure)
			{
				$code = $this->handler;

				return $code($this);
			}
			elseif (is_callable($this->handler))
			{
				return call_user_func($this->handler, $this);
			}
		}

		return $this->doExecute();
	}

	/**
	 * Execute this command.
	 *
	 * @throws \LogicException
	 *
	 * @return mixed
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function doExecute()
	{
		throw new \LogicException('You must override the doExecute() method in the concrete command class.');
	}

	/**
	 * Configure command.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function configure()
	{
	}

	/**
	 * Execute the sub command.
	 *
	 * @param   string      $name    The command name.
	 * @param   IOInterface $io      The Cli IO object.
	 *
	 * @throws  CommandNotFoundException
	 * @return  mixed
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function executeSubCommand($name, IOInterface $io = null)
	{
		if (empty($this->children[$name]))
		{
			throw new CommandNotFoundException(sprintf('Command "%s" not found.', $name), $this, $name);
		}

		/** @var $subCommand AbstractCommand */
		$subCommand = $this->children[$name];

		// Remove first argument and send it to child
		if (!$io)
		{
			$io = $this->io;

			$io->shiftArgument();
		}

		$subCommand->setIO($io)
			->setApplication($this->application);

		return $subCommand->execute();
	}

	/**
	 * Method to get property Io
	 *
	 * @return  \Windwalker\IO\Cli\IOInterface
	 */
	public function getIO()
	{
		return $this->io;
	}

	/**
	 * Method to set property io
	 *
	 * @param   \Windwalker\IO\Cli\IOInterface $io
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setIO($io)
	{
		$this->io = $io;

		return $this;
	}

	/**
	 * Parent command setter.
	 *
	 * @param   AbstractCommand  $parent  The parent comment.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setParent(AbstractCommand $parent = null)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Get Parent Command.
	 *
	 * @return  AbstractCommand
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Add an argument(sub command) setting.
	 *
	 * @param   string|AbstractCommand  $command      The argument name or Console object.
	 *                                                If we just send a string, the object will auto create.
	 * @param   string                  $description  Console description.
	 * @param   Option[]                $options      Console options.
	 * @param   \Closure                $handler      The closure to execute.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addCommand($command, $description = null, $options = array(), \Closure $handler = null)
	{
		if (!($command instanceof AbstractCommand))
		{
			$command = new static($command, $this->io, $this);
		}

		// Set argument detail
		$command->setApplication($this->application)
			->setIO($this->io);

		if ($description !== null)
		{
			$command->setDescription($description);
		}

		if (count($options))
		{
			$command->setOptions($options);
		}

		if ($handler)
		{
			$command->setHandler($handler);
		}

		// Set parent
		$command->setParent($this);

		// Set global options to sub command
		/** @var $option Option */
		foreach ($this->globalOptions as $option)
		{
			$command->addOption($option);
		}

		$name  = $command->getName();

		$this->children[$name] = $command;

		return $this;
	}

	/**
	 * Get argument by offset or return default.
	 *
	 * @param   int             $offset   Argument offset.
	 * @param   callable|mixed  $default  Default value, if is a callable, will execute it.
	 *
	 * @return  null|string  Values from argument or user input.
	 */
	public function getArgument($offset, $default = null)
	{
		$value = $this->io->getArgument($offset);

		if (!is_null($value))
		{
			return $value;
		}

		if (is_callable($default))
		{
			return $default();
		}

		return $default;
	}

	/**
	 * Alias of addCommand if someone think child is more semantic.
	 *
	 * @param   string|AbstractCommand  $argument     The argument name or Console object.
	 *                                                If we just send a string, the object will auto create.
	 * @param   string                  $description  Console description.
	 * @param   Option[]                $options      Console options.
	 * @param   \Closure                $handler      The closure to execute.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addChild($argument, $description = null, $options = array(), \Closure $handler = null)
	{
		return $this->addCommand($argument, $description, $options, $handler);
	}

	/**
	 * Get a argument(command) by name path.
	 *
	 * @param   string  $path  Command name path.
	 *
	 * @return  AbstractCommand|null  Return command or null.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getChild($path)
	{
		$path    = str_replace(array('/', '\\'), '\\', $path);
		$names   = explode('\\', $path);
		$command = $this;

		foreach ($names as $name)
		{
			if (isset($command[$name]))
			{
				$command = $command[$name];

				continue;
			}

			return null;
		}

		return $command;
	}

	/**
	 * Get children array.
	 *
	 * @return array  children.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Batch set children (sub commands).
	 *
	 * @param   array  $children  An array include argument objects.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setChildren($children)
	{
		$children = (array) $children;

		foreach ($children as $argument)
		{
			$this->addCommand($argument);
		}

		return $this;
	}

	/**
	 * Add a option object to this command.
	 *
	 * @param   mixed   $option       The option name. Can be a string, an array or an object.
	 *                                 If we use array, the first element will be option name, others will be alias.
	 * @param   mixed   $default      The default value when we get a non-exists option.
	 * @param   string  $description  The option description.
	 * @param   bool    $global       If true, this option will be a global option that sub commends will extends it.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addOption($option, $default = null, $description = null, $global = false)
	{
		if (!($option instanceof Option))
		{
			$option = new Option($option, $default, $description, $global);
		}

		$option->setIO($this->io);

		$name   = $option->getName();
		$global = $option->isGlobal();

		if ($global)
		{
			$this->globalOptions[$name] = $option;

			// Global option should not equal to private option
			unset($this->options[$name]);

			// We should pass global option to all children.
			foreach ($this->children as $child)
			{
				$child->addOption($option);
			}
		}
		else
		{
			$this->options[$name] = $option;

			// Global option should not equal to private option
			unset($this->globalOptions[$name]);
		}

		return $this;
	}

	/**
	 * Get value from an option.
	 *
	 * If the name not found, we use alias to find options.
	 *
	 * @param   string  $name     The option name.
	 * @param   string  $default  The default value when option not set.
	 *
	 * @return  mixed  The option value we want to get or default value if option not exists.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getOption($name, $default = null)
	{
		// Get from private
		$option = $this->options[$name];

		if (!$option)
		{
			$option = $this->globalOptions[$name];
		}

		if ($option instanceof Option)
		{
			$option->setIO($this->io);

			return $option->getValue();
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Get options as array.
	 *
	 * @param   boolean  $global  is Global options.
	 *
	 * @return  mixed  The options array.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getOptions($global = false)
	{
		return $global ? (array) $this->options : (array) $this->globalOptions;
	}

	/**
	 * Get option set object.
	 *
	 * @param   boolean  $global  is Global options.
	 *
	 * @return  mixed  The options array.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getOptionSet($global = false)
	{
		return $global ? $this->globalOptions : $this->options;
	}

	/**
	 * Get all options include global.
	 *
	 * @return array  The options array.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getAllOptions()
	{
		return array_merge((array) $this->globalOptions, (array) $this->options);
	}

	/**
	 * Batch add options to command.
	 *
	 * @param   mixed  $options  An options array.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function setOptions($options)
	{
		$options = is_array($options) ? $options : array($options);

		foreach ($options as $option)
		{
			$this->addOption($option);
		}

		return $this;
	}

	/**
	 * set the option alias.
	 *
	 * @param   mixed   $aliases  The alias to map this option.
	 * @param   string  $name     The option name.
	 * @param   bool    $global   Is global option?
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setOptionAlias($aliases, $name, $global = false)
	{
		if ($global)
		{
			$this->globalOptions->setAlias($aliases, $name);
		}
		else
		{
			$this->options->setAlias($aliases, $name);
		}

		return $this;
	}

	/**
	 * The command description getter.
	 *
	 * @return string  Console description.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * The command description setter.
	 *
	 * @param   string  $description  Console description.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Console name getter.
	 *
	 * @return string  Console name.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Console name setter.
	 *
	 * @param   string  $name  Console name.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Console execute code getter.
	 *
	 * @return  \Closure  Console execute code.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Console execute code setter.
	 *
	 * @param   callable  $handler  Console execute handler.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setHandler($handler = null)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Get the application.
	 *
	 * @return Console  Console application.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * Set the application.
	 *
	 * @param   Console  $application  Application object.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setApplication($application)
	{
		$this->application = $application;

		return $this;
	}

	/**
	 * Get the help manual.
	 *
	 * @return string  Help of this Command.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getHelp()
	{
		return $this->help;
	}

	/**
	 * Sets the help manual
	 *
	 * @param   string  $help  The help manual.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setHelp($help)
	{
		$this->help = $help;

		return $this;
	}

	/**
	 * Get the usage.
	 *
	 * @return string  Usage of this command.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getUsage()
	{
		return sprintf($this->usage, $this->getName());
	}

	/**
	 * Sets the usage to tell user how to use this command.
	 *
	 * @param   string  $usage  Usage of this command.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setUsage($usage)
	{
		$this->usage = $usage;

		return $this;
	}

	/**
	 * Render auto complete alternatives.
	 *
	 * @param   string                    $wrongName  The wrong command name to auto completed.
	 * @param   CommandNotFoundException  $exception  The exception of wrong argument.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function renderAlternatives($wrongName, $exception)
	{
		/** @var $exception \InvalidArgumentException */
		$message      = $exception->getMessage();
		$autoComplete = '';
		$alternatives = array();

		// Autocomplete
		foreach ($this->children as $command)
		{
			/** @var $command Command */
			$commandName = $command->getName();

			/*
			 * Here we use "Levenshtein distance" to compare wrong name with every command names.
			 *
			 * If the difference number less than 1/3 of wrong name which user typed, means this is a similar name,
			 * we can notice user to choose these similar names.
			 *
			 * And if the string of wrong name can be found in a command name, we also notice user to choose it.
			 */
			if (levenshtein($wrongName, $commandName) <= (strlen($wrongName) / 3) || strpos($commandName, $wrongName) !== false)
			{
				$alternatives[] = "    " . $commandName;
			}
		}

		if (count($alternatives))
		{
			$autoComplete = "Did you mean one of these?\n";
			$autoComplete .= implode($alternatives);
		}

		$this->out('');
		$this->err("<error>{$message}</error>");
		$this->out('');
		$this->err($autoComplete);
	}

	/**
	 * Render exception for debugging.
	 *
	 * @param   \Exception  $exception  The exception we want to render.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function renderException($exception)
	{
		/** @var $exception \Exception */
		$class = get_class($exception);

		$output = <<<EOF
<error>Exception '{$class}' with message:</error> <fg=cyan;options=bold>{$exception->getMessage()}</fg=cyan;options=bold>
<info>in {$exception->getFile()}:{$exception->getLine()}</info>

<error>Stack trace:</error>
{$exception->getTraceAsString()}
EOF;

		$this->out('');
		$this->err($output);
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  AbstractCommand  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function out($text = '', $nl = true)
	{
		$this->io->out($text, $nl);

		return $this;
	}

	/**
	 * Write a string to standard error output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  AbstractCommand  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function err($text = '', $nl = true)
	{
		$this->io->out($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function in($question = '')
	{
		if ($question)
		{
			$this->out($question, false);
		}

		return rtrim(fread(STDIN, 8192), "\n\r");
	}

	/**
	 * Set child command, note the key is no use, we use command name as key.
	 *
	 * @param   mixed            $offset  No use here.
	 * @param   AbstractCommand  $value   Command object.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetSet($offset, $value)
	{
		$this->addCommand($value);
	}

	/**
	 * Is a child exists?
	 *
	 * @param   string  $offset  The command name to get command.
	 *
	 * @return  boolean  True if command exists.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetExists($offset)
	{
		return isset($this->children[$offset]);
	}

	/**
	 * Unset a child command.
	 *
	 * @param   string  $offset  The command name to remove.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetUnset($offset)
	{
		unset($this->children[$offset]);
	}

	/**
	 * Get a command by name.
	 *
	 * @param   string  $offset  The command name to get command.
	 *
	 * @return  AbstractCommand|null  Return command object if found.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetGet($offset)
	{
		return isset($this->children[$offset]) ? $this->children[$offset] : null;
	}
}
