<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

/**
 * Class Event
 *
 * @since {DEPLOY_VERSION}
 */
class Event implements EventInterface, \ArrayAccess, \Serializable, \Countable
{
	/**
	 * The event name.
	 *
	 * @var    string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $name;

	/**
	 * The event arguments.
	 *
	 * @var    array
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $arguments;

	/**
	 * A flag to see if the event propagation is stopped.
	 *
	 * @var    boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $stopped = false;

	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($name, array $arguments = array())
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get an event argument value.
	 *
	 * @param   string  $name     The argument name.
	 * @param   mixed   $default  The default value if not found.
	 *
	 * @return  mixed  The argument value or the default value.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getArgument($name, $default = null)
	{
		if (isset($this->arguments[$name]))
		{
			return $this->arguments[$name];
		}

		return $default;
	}

	/**
	 * Tell if the given event argument exists.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  boolean  True if it exists, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function hasArgument($name)
	{
		return isset($this->arguments[$name]);
	}

	/**
	 * Get all event arguments.
	 *
	 * @return  array  An associative array of argument names as keys
	 *                 and their values as values.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Add an event argument, only if it is not existing.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  Event  This method is chainable.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addArgument($name, $value)
	{
		if (!isset($this->arguments[$name]))
		{
			$this->arguments[$name] = $value;
		}

		return $this;
	}

	/**
	 * Set the value of an event argument.
	 * If the argument already exists, it will be overridden.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  Event  This method is chainable.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setArgument($name, $value)
	{
		$this->arguments[$name] = $value;

		return $this;
	}

	/**
	 * Remove an event argument.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  mixed  The old argument value or null if it is not existing.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function removeArgument($name)
	{
		$return = null;

		if (isset($this->arguments[$name]))
		{
			$return = $this->arguments[$name];

			unset($this->arguments[$name]);
		}

		return $return;
	}

	/**
	 * Clear all event arguments.
	 *
	 * @return  array  The old arguments.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function clearArguments()
	{
		$arguments = $this->arguments;

		$this->arguments = array();

		return $arguments;
	}

	/**
	 * Stop the event propagation.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function stop()
	{
		$this->stopped = true;
	}

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean  True if stopped, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function isStopped()
	{
		return true === $this->stopped;
	}

	/**
	 * Count the number of arguments.
	 *
	 * @return  integer  The number of arguments.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function count()
	{
		return count($this->arguments);
	}

	/**
	 * Serialize the event.
	 *
	 * @return  string  The serialized event.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function serialize()
	{
		return serialize(array($this->name, $this->arguments, $this->stopped));
	}

	/**
	 * Unserialize the event.
	 *
	 * @param   string  $serialized  The serialized event.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function unserialize($serialized)
	{
		list($this->name, $this->arguments, $this->stopped) = unserialize($serialized);
	}

	/**
	 * Tell if the given event argument exists.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  boolean  True if it exists, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetExists($name)
	{
		return $this->hasArgument($name);
	}

	/**
	 * Get an event argument value.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  mixed  The argument value or null if not existing.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetGet($name)
	{
		return $this->getArgument($name);
	}

	/**
	 * Set the value of an event argument.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException  If the argument name is null.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetSet($name, $value)
	{
		if (is_null($name))
		{
			throw new \InvalidArgumentException('The argument name cannot be null.');
		}

		$this->setArgument($name, $value);
	}

	/**
	 * Remove an event argument.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function offsetUnset($name)
	{
		$this->removeArgument($name);
	}
}

