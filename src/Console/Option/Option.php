<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Option;

use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;

/**
 * The cli option class.
 *
 * @since  2.0
 */
class Option
{
	const IS_GLOBAL = true;

	const IS_PRIVATE = false;

	/**
	 * Option name.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $name;

	/**
	 * Option aliases.
	 *
	 * @var  array
	 *
	 * @since  2.0
	 */
	protected $aliases = array();

	/**
	 * Option description.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $description;

	/**
	 * Global option or not.
	 *
	 * @var  boolean
	 *
	 * @since  2.0
	 */
	protected $global;

	/**
	 * The default when option not sent.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $default;

	/**
	 * Cli Input object.
	 *
	 * @var IOInterface
	 *
	 * @since  2.0
	 */
	protected $io;

	/**
	 * The option value cache.
	 *
	 * @var string
	 *
	 * @since  2.0
	 */
	protected $value;

	/**
	 * Class Constructor.
	 *
	 * @param   mixed    $aliases      The option name. Can be a string, an array or an object.
	 *                                 If we use array, the first element will be option name, others will be alias.
	 * @param   mixed    $default      The default value when we get a non-exists option.
	 * @param   string   $description  The option description.
	 * @param   boolean  $global       True is a global option.
	 *
	 * @since   2.0
	 */
	public function __construct($aliases, $default = null, $description = null, $global = false)
	{
		$aliases = (array) $aliases;
		$name  = array_shift($aliases);

		$this->name        = $name;
		$this->default     = $default;
		$this->description = $description;
		$this->global      = $global;

		if (count($aliases))
		{
			$this->setAliases($aliases);
		}
	}

	/**
	 * Alias setter.
	 *
	 * @param   string  $alias  The option alias.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function setAliases($alias)
	{
		$this->aliases = $alias;

		return $this;
	}

	/**
	 * Alias getter.
	 *
	 * @return array  The option alias.
	 *
	 * @since  2.0
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Has alias.
	 *
	 * @param   string  $alias  The option alias to find.
	 *
	 * @return  boolean
	 *
	 * @since  2.0
	 */
	public function hasAlias($alias)
	{
		if ($this->name == $alias)
		{
			return true;
		}

		return in_array($alias, $this->aliases);
	}

	/**
	 * Add a new alias.
	 *
	 * @param   string $alias The alias name.
	 *
	 * @return  static
	 */
	public function alias($alias)
	{
		$this->aliases[] = $alias;

		return $this;
	}

	/**
	 * Default value getter, alias of defaultValue().
	 *
	 * @param   mixed  $default  The default value.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 *
	 * @deprecated  2.2 Use defaultValue() instead
	 */
	public function setDefault($default)
	{
		return $this->defaultValue($default);
	}

	/**
	 * Default value getter.
	 *
	 * @param   mixed  $default  The default value.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function defaultValue($default)
	{
		$this->default = $default;

		return $this;
	}

	/**
	 * Default value getter.
	 *
	 * @return string  The default value.
	 *
	 * @since  2.0
	 */
	public function getDefaultValue()
	{
		return $this->default;
	}

	/**
	 * Description setter.
	 *
	 * @param   string  $description  The description.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function description($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Description getter.
	 *
	 * @return  string  The description.
	 *
	 * @since   2.0
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Name setter.
	 *
	 * @param   string  $name  Name of this option.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Name getter.
	 *
	 * @return  string  Name of this option.
	 *
	 * @since   2.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get Cli Input object.
	 *
	 * @return  IOInterface  The Cli IO object.
	 *
	 * @since   2.0
	 */
	public function getIO()
	{
		if (!$this->io)
		{
			$this->io = new IO;
		}

		return $this->io;
	}

	/**
	 * Set Cli Input object.
	 *
	 * @param   IOInterface  $io  The Cli IO object.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function setIO(IOInterface $io)
	{
		$this->io = $io;

		return $this;
	}

	/**
	 * Get the value of this option which sent from command line.
	 *
	 * @return  mixed  The value of this option.
	 *
	 * @since   2.0
	 */
	public function getValue()
	{
		$io = $this->getIO();

		$name = $this->name;

		if ($io->getOption($name) !== null)
		{
			return $io->getOption($name);
		}

		foreach ($this->aliases as $alias)
		{
			if ($io->getOption($alias) !== null)
			{
				return $io->getOption($alias);
			}
		}

		return $this->default;
	}

	/**
	 * Is this a global option?
	 *
	 * @return  bool  True is a global option.
	 *
	 * @since   2.0
	 */
	public function isGlobal()
	{
		return $this->global;
	}

	/**
	 * Set this option is global or not.
	 *
	 * @param   boolean  $global  True is a global option.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   2.0
	 */
	public function setGlobal($global)
	{
		$this->global = $global;

		return $this;
	}
}
