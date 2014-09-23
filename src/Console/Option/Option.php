<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Option;

use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;

/**
 * The cli option class.
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	protected $name;

	/**
	 * Option alias.
	 *
	 * @var  array
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $alias = array();

	/**
	 * Option description.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $description;

	/**
	 * Global option or not.
	 *
	 * @var  boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $global;

	/**
	 * The default when option not sent.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $default;

	/**
	 * Cli Input object.
	 *
	 * @var IOInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $io;

	/**
	 * The option value cache.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $value;

	/**
	 * Class Constructor.
	 *
	 * @param   mixed    $alias        The option name. Can be a string, an array or an object.
	 *                                  If we use array, the first element will be option name, others will be alias.
	 * @param   mixed    $default      The default value when we get a non-exists option.
	 * @param   string   $description  The option description.
	 * @param   boolean  $global       True is a global option.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($alias, $default = null, $description = null, $global = false)
	{
		$alias = (array) $alias;
		$name  = array_shift($alias);

		$this->name        = $name;
		$this->default     = $default;
		$this->description = $description;
		$this->global      = $global;

		if (count($alias))
		{
			$this->setAlias($alias);
		}
	}

	/**
	 * Alias setter.
	 *
	 * @param   string  $alias  The option alias.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Alias getter.
	 *
	 * @return array  The option alias.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * Default value getter.
	 *
	 * @param   mixed  $default  The default value.
	 *
	 * @return  Option  Return this object to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setDefault($default)
	{
		$this->default = $default;

		return $this;
	}

	/**
	 * Default value getter.
	 *
	 * @return string  The default value.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function getDefault()
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Description getter.
	 *
	 * @return  string  The description.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function getValue()
	{
		$io = $this->getIO();

		$name = $this->name;

		if ($io->getOption($name))
		{
			return $io->getOption($name);
		}

		foreach ($this->alias as $alias)
		{
			if ($io->getOption($alias))
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function setGlobal($global)
	{
		$this->global = $global;

		return $this;
	}
}
