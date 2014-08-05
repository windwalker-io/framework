<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Option;

/**
 * Option set to store options and resolve aliases.
 *
 * @since {DEPLOY_VERSION}
 */
class OptionSet extends \ArrayObject
{
	/**
	 * Option aliases.
	 *
	 * @var    array
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $aliases = array();

	/**
	 * Add a new option.
	 *
	 * @param   Option  $option  Option object.
	 *
	 * @return  OptionSet Return self to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addOption(Option $option)
	{
		$this->offsetSet($option->getName(), $option);

		return $this;
	}

	/**
	 * Is a option exists?
	 *
	 * @param   mixed  $name  Option name.
	 *
	 * @return  boolean True if option exists.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetExists($name)
	{
		$name = $this->resolveAlias($name);

		return parent::offsetExists($name);
	}

	/**
	 * Get an option by name.
	 *
	 * @param   mixed   $name  Option name to get option.
	 *
	 * @return  Option|null  Return option object if exists.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetGet($name)
	{
		$name = $this->resolveAlias($name);

		if (!$this->offsetExists($name))
		{
			return null;
		}

		return parent::offsetGet($name);
	}

	/**
	 * Set a new option.
	 *
	 * @param   string  $name    No use here, we use option name.
	 * @param   Option  $option  The option object to set in this set.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetSet($name, $option)
	{
		$name = $option->getName();

		$aliases = $option->getAlias();

		$this->setAlias($aliases, $name);

		parent::offsetSet($name, $option);
	}

	/**
	 * Remove an option.
	 *
	 * @param   string  $name  Option name to remove from this set.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetUnset($name)
	{
		$name = $this->resolveAlias($name);

		if (!$this->offsetExists($name))
		{
			return;
		}

		parent::offsetUnset($name);
	}

	/**
	 * Set Alias of an option.
	 *
	 * @param   array|string  $aliases  An alias of a option, can be array.
	 * @param   string        $option   The option which we want to add alias.
	 *
	 * @return  OptionSet  Return self to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setAlias($aliases, $option)
	{
		$aliases = (array) $aliases;

		foreach ($aliases as $alias)
		{
			$this->aliases[$alias] = $option;
		}

		return $this;
	}

	/**
	 * Resolve alias for an option.
	 *
	 * @param   string  $alias  An alias to help us get option name.
	 *
	 * @return  string  Return name if found, or return alias as name.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function resolveAlias($alias)
	{
		if (!empty($this->aliases[$alias]))
		{
			return $this->aliases[$alias];
		}

		return $alias;
	}
}
