<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Uri;

/**
 * Uri Class
 *
 * This is an immutable version of the uri class.
 *
 * This class is a fork from Joomla Uri.
 *
 * @since  {DEPLOY_VERSION}
 */
final class UriImmutable extends AbstractUri
{
	/**
	 * @var    boolean  Has this class been instantiated yet.
	 * @since  {DEPLOY_VERSION}
	 */
	private $constructed = false;

	/**
	 * Prevent setting undeclared properties.
	 *
	 * @param   string  $name   This is an immutable object, setting $name is not allowed.
	 * @param   mixed   $value  This is an immutable object, setting $value is not allowed.
	 *
	 * @return  null  This method always throws an exception.
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \BadMethodCallException
	 */
	public function __set($name, $value)
	{
		throw new \BadMethodCallException('This is an immutable object');
	}

	/**
	 * This is a special constructor that prevents calling the __construct method again.
	 *
	 * @param   string  $uri  The optional URI string
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \BadMethodCallException
	 */
	public function __construct($uri = null)
	{
		if ($this->constructed === true)
		{
			throw new \BadMethodCallException('This is an immutable object');
		}

		$this->constructed = true;

		parent::__construct($uri);
	}
}
