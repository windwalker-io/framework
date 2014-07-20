<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DI;

/**
 * Class ContainerAwareTrait
 *
 * @since 1.0
 */
trait ContainerAwareTrait
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.2
	 */
	private $container;

	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   1.2
	 *
	 * @throws  \UnexpectedValueException May be thrown if the container has not been set.
	 */
	public function getContainer()
	{
		if ($this->container)
		{
			return $this->container;
		}

		throw new \UnexpectedValueException('Container not set in ' . get_called_class());
	}

	/**
	 * Set the DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  mixed  Returns itself to support chaining.
	 *
	 * @since   1.2
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
} 