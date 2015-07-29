<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Command;

use Windwalker\Console\AbstractConsole;
use Windwalker\Console\Console;
use Windwalker\Console\Exception\CommandNotFoundException;
use Windwalker\Console\Option\Option;
use Windwalker\Console\Option\OptionSet;
use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;

/**
 * Abstract Console class.
 *
 * @since  2.0
 */
abstract class AbstractCommand implements \ArrayAccess
{
	/**
	 * Console application.
	 *
	 * @var  Console
	 *
	 * @since  2.0
	 */
	public $app;

	/**
	 * The Cli input object.
	 *
	 * @var IOInterface
	 *
	 * @since  2.0
	 */
	protected $io;

	/**
	 * Console(Argument) name.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $name;

	/**
	 * The children(SubCommends) storage.
	 *
	 * @var AbstractCommand[]
	 *
	 * @since  2.0
	 */
	protected $children = array();

	/**
	 * The Options storage.
	 *
	 * @var OptionSet
	 *
	 * @since  2.0
	 */
	protected $options = null;

	/**
	 * Global Options.
	 *
	 * @var OptionSet
	 *
	 * @since  2.0
	 */
	protected $globalOptions = null;

	/**
	 * The command description.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $description;

	/**
	 * The manual about this command.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $help;

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 *
	 * @since  2.0
	 */
	protected $usage = '%s <cmd><command></cmd> <option>[option]</option>';

	/**
	 * The closure to execute.
	 *
	 * @var  callable
	 *
	 * @since  2.0
	 */
	protected $handler;

	/**
	 * The parent Console if this is a sub comment.
	 *
	 * @var AbstractCommand
	 *
	 * @since  2.0
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
	 * @since  2.0
	 */
	public function __construct($name = null, IOInterface $io = null, AbstractCommand $parent = null)
	{
		$this->name   = $name ? : $this->name;
		$this->io     = $io ? : new IO;
		$this->parent = $parent;

		$this->options       = new OptionSet;
		$this->globalOptions = new OptionSet;

		$this->initialise();

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
	 * @since  2.0
	 */
	public function execute()
	{
		$this->prepareExecute();

		// Show help or not
		if (!count($this->children) && $this->app instanceof AbstractConsole && $this->app->get('show_help'))
		{
			$this->io->out($this->app->describeCommand($this));

			return $this->postExecute(true);
		}

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
				throw $e;
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

		$result = $this->doExecute();

		return $this->postExecute($result);
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
	 * @param   mixed  $result  Executed return value.
	 *
	 * @return  mixed
	 */
	protected function postExecute($result = null)
	{
		return $result;
	}

	/**
	 * Execute this command.
	 *
	 * @throws \LogicException
	 *
	 * @return mixed
	 *
	 * @since  2.0
	 */
	protected function doExecute()
	{
		throw new \LogicException('You must override the doExecute() method in the concrete command class.');
	}

	/**
	 * Initialise command.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	protected function initialise()
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
	 * @since  2.0
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
			->setApplication($this->app);

		return $subCommand->execute();
	}

	/**
	 * Method to get property Io
	 *
	 * @return  \Windwalker\Console\IO\IOInterface
	 */
	public function getIO()
	{
		return $this->io;
	}

	/**
	 * Method to set property io
	 *
	 * @param   \Windwalker\Console\IO\IOInterface $io
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function addCommand($command, $description = null, $options = array(), \Closure $handler = null)
	{
		if (!($command instanceof AbstractCommand))
		{
			$command = new static($command, $this->io, $this);
		}

		// Set argument detail
		$command->setApplication($this->app)
			->setIO($this->io);

		if ($description !== null)
		{
			$command->description($description);
		}

		if (count($options))
		{
			$command->setOptions($options);
		}

		if ($handler)
		{
			$command->handler($handler);
		}

		// Set parent
		$command->setParent($this);

		// Set global options to sub command
		/** @var $option Option */
		foreach ($this->globalOptions as $option)
		{
			$command->addGlobalOption($option);
		}

		$name  = $command->getName();

		$this->children[$name] = $command;

		return $command;
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
	 * @since   2.0
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
	 * @since  2.0
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
	 * @since  2.0
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
	 * @since   2.0
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
	 *
	 * @return  Option  Return Option object.
	 *
	 * @since   2.0
	 */
	public function addOption($option, $default = null, $description = null)
	{
		if (!($option instanceof Option))
		{
			$option = new Option($option, $default, $description);
		}

		$option->setGlobal(Option::IS_PRIVATE);

		$option->setIO($this->io);

		$name = $option->getName();

		$this->options[$name] = $option;

		// Global option should not equal to private option
		unset($this->globalOptions[$name]);

		return $option;
	}

	/**
	 * Add a option object to this command.
	 *
	 * @param   mixed   $option       The option name. Can be a string, an array or an object.
	 *                                 If we use array, the first element will be option name, others will be alias.
	 * @param   mixed   $default      The default value when we get a non-exists option.
	 * @param   string  $description  The option description.
	 *
	 * @return  Option  Return Option object.
	 *
	 * @since   2.0
	 */
	public function addGlobalOption($option, $default = null, $description = null)
	{
		if (!($option instanceof Option))
		{
			$option = new Option($option, $default, $description);
		}

		$option->setGlobal(Option::IS_GLOBAL);

		$option->setIO($this->io);

		$name = $option->getName();

		$this->globalOptions[$name] = $option;

		// Global option should not equal to private option
		unset($this->options[$name]);

		// We should pass global option to all children.
		foreach ($this->children as $child)
		{
			$child->addGlobalOption($option);
		}

		return $option;
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
	 * @since   2.0
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

			$value = $option->getValue();

			if ($value === null)
			{
				return $default;
			}

			return $value;
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
	 * @return  Option[]  The options array.
	 *
	 * @since   2.0
	 */
	public function getOptions($global = false)
	{
		return $global ? $this->options->toArray() : $this->globalOptions->toArray();
	}

	/**
	 * Get option set object.
	 *
	 * @param   boolean  $global  is Global options.
	 *
	 * @return  OptionSet  The options array.
	 *
	 * @since   2.0
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
	 * @since  2.0
	 */
	public function getAllOptions()
	{
		return array_merge($this->globalOptions->toArray(), $this->options->toArray());
	}

	/**
	 * Batch add options to command.
	 *
	 * @param   mixed  $options  An options array.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function setOptionAliases($aliases, $name, $global = false)
	{
		if ($global)
		{
			$this->globalOptions->setAliases($aliases, $name);
		}
		else
		{
			$this->options->setAliases($aliases, $name);
		}

		return $this;
	}

	/**
	 * The command description getter.
	 *
	 * @return string  Console description.
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function description($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Console name getter.
	 *
	 * @return string  Console name.
	 *
	 * @since  2.0
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
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
	 */
	public function handler($handler = null)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Get the application.
	 *
	 * @return Console  Console application.
	 *
	 * @since  2.0
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Set the application.
	 *
	 * @param   Console  $application  Application object.
	 *
	 * @return  AbstractCommand  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function setApplication($application)
	{
		$this->app = $application;

		return $this;
	}

	/**
	 * Get the help manual.
	 *
	 * @return string  Help of this Command.
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function help($help)
	{
		$this->help = $help;

		return $this;
	}

	/**
	 * Get the usage.
	 *
	 * @return string  Usage of this command.
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	public function usage($usage)
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
	 * @since   2.0
	 */
	public function renderAlternatives($wrongName, $exception)
	{
		/** @var $exception \InvalidArgumentException */
		$message      = $exception->getMessage();
		$autoComplete = '';
		$alternatives = array();

		// Auto complete
		foreach ($this->children as $command)
		{
			/** @var $command Command */
			$commandName = $command->getName();
			$denominator = 3;

			/*
			 * Here we use "Levenshtein distance" to compare wrong name with every commands' name.
			 *
			 * If the difference number less than 1/3 of the wrong name which user typed, means this is a similar name,
			 * we can notice user to choose these similar names.
			 *
			 * And if the string of wrong name can be found in a command name, we also notice user to choose it.
			 */
			if (levenshtein($wrongName, $commandName) <= (strlen($wrongName) / $denominator) || strpos($commandName, $wrongName) !== false)
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
	 * @since   2.0
	 */
	public function renderException(\Exception $exception)
	{
		$verbose = $this->app ? $this->app->get('verbose', 0) : 0;

		if (!$verbose)
		{
			$this->out()->err($exception->getMessage());

			return;
		}

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
	 * Raise error message.
	 *
	 * @param   \Exception|string  $exception  Exception object or message string.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function error($exception)
	{
		if (!($exception instanceof $exception))
		{
			$exception = new \Exception($exception);
		}

		throw $exception;
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  AbstractCommand  Instance of $this to allow chaining.
	 *
	 * @since   2.0
	 */
	public function out($text = '', $nl = true)
	{
		$quiet = $this->app ? $this->app->get('quiet', false) : false;

		if (!$quiet)
		{
			$this->io->out($text, $nl);
		}

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
	 * @since   2.0
	 */
	public function err($text = '', $nl = true)
	{
		$this->io->err($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
	 */
	public function offsetGet($offset)
	{
		return isset($this->children[$offset]) ? $this->children[$offset] : null;
	}
}
