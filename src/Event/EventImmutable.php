<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Event;

/**
 * Class EventImmutable
 *
 * @since {DEPLOY_VERSION}
 */
class EventImmutable extends Event
{
	/**
	 * A flag to see if the constructor has been
	 * already called.
	 *
	 * @var  boolean
	 */
	private $constructed = false;

	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  \BadMethodCallException
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct($name, array $arguments = array())
	{
		if ($this->constructed)
		{
			throw new \BadMethodCallException(
				sprintf('Cannot reconstruct the EventImmutable %s.', $this->name)
			);
		}

		$this->constructed = true;

		parent::__construct($name, $arguments);
	}

	/**
	 * setArgument
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  EventImmutable
	 */
	public function setArgument($name, $value)
	{
		return $this;
	}

	/**
	 * Add an event argument, only if it is not existing.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  EventImmutable  This method is chainable.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function addArgument($name, $value)
	{
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
		return null;
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
		return $this->arguments;
	}
}

