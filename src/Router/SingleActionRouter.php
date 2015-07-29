<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router;

use Windwalker\Router\Matcher\MatcherInterface;
use Windwalker\Router\Matcher\SequentialMatcher;

/**
 * Class SingleActionRouter
 *
 * @since 2.0
 */
class SingleActionRouter extends Router
{
	/**
	 * Property requests.
	 *
	 * @var  array
	 */
	protected $variables = array();

	/**
	 * Class init.
	 *
	 * @param array            $routes
	 * @param MatcherInterface $matcher
	 */
	public function __construct(array $routes = array(), MatcherInterface $matcher = null)
	{
		$this->addMaps($routes);

		$this->matcher = $matcher ? : new SequentialMatcher;
	}

	/**
	 * addRoute
	 *
	 * @param string $pattern
	 * @param string $controller
	 *
	 * @throws  \LogicException
	 * @throws  \InvalidArgumentException
	 * @return  static
	 */
	public function addMap($pattern, $controller = null)
	{
		if (!is_string($controller))
		{
			throw new \InvalidArgumentException('Please give me controller name string. ' . ucfirst(gettype($controller)) . ' given.');
		}

		if ($pattern instanceof Route)
		{
			throw new \LogicException('Do not use Route object in ' . get_called_class());
		}

		return parent::addRoute(null, $pattern, array('_controller' => $controller));
	}

	/**
	 * parseRoute
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @throws  \UnexpectedValueException
	 * @return  array|boolean
	 */
	public function match($route, $method = 'GET', $options = array())
	{
		$matched = parent::match($route, $method, $options);

		$vars = $matched->getVariables();

		if (!array_key_exists('_controller', $vars))
		{
			throw new \UnexpectedValueException('Controller not found.', 500);
		}

		$controller = $vars['_controller'];

		unset($vars['_controller']);

		$this->setVariables($vars);

		return $controller;
	}

	/**
	 * getRequests
	 *
	 * @return  array
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * setRequests
	 *
	 * @param   array $variables
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setVariables($variables)
	{
		$this->variables = $variables;

		return $this;
	}
}

