<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model\Filter;

/**
 * Class AbstractQueryHelper
 *
 * @since 1.0
 */
abstract class AbstractFilterHelper implements FilterHelperInterface
{
	const SKIP = false;

	/**
	 * Property handler.
	 *
	 * @var  array
	 */
	protected $handler = array();

	/**
	 * Property defaultHandler.
	 *
	 * @var  \Closure
	 */
	protected $defaultHandler = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->defaultHandler = $this->registerDefaultHandler();
	}

	/**
	 * setHandler
	 *
	 * @param string           $name
	 * @param callable|boolean $handler
	 *
	 * @throws \InvalidArgumentException
	 * @return  AbstractFilterHelper
	 */
	public function setHandler($name, $handler)
	{
		$this->handler[$name] = $handler;

		return $this;
	}

	/**
	 * registerDefaultHandler
	 *
	 * @return  callable
	 */
	abstract protected function registerDefaultHandler();

	/**
	 * setDefaultHandler
	 *
	 * @param   callable $defaultHandler
	 *
	 * @return  AbstractFilterHelper  Return self to support chaining.
	 */
	public function setDefaultHandler($defaultHandler)
	{
		$this->defaultHandler = $defaultHandler;

		return $this;
	}
}
