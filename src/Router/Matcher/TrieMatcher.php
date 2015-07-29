<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Compiler\TrieCompiler;
use Windwalker\Router\Route;
use Windwalker\Router\RouteHelper;

/**
 * The TrieMatcher class.
 * 
 * @since  2.0
 */
class TrieMatcher extends AbstractMatcher
{
	/**
	 * Property tree.
	 *
	 * @var  array
	 */
	protected $tree = array();

	/**
	 * Property vars.
	 *
	 * @var  array
	 */
	protected $vars = array();

	/**
	 * Property method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Match routes.
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @return  Route|false
	 */
	public function match($route, $method = 'GET', $options = array())
	{
		$this->method = $method;
		$this->options = $options;
		$this->count = 0;

		// Init some data
		$this->buildRouteMaps()
			->buildTree();

		// Match
		$segments = explode('/', RouteHelper::sanitize($route));

		$routeItem = $this->matchSegment($segments, $this->tree);

		if (!$routeItem)
		{
			return false;
		}

		$routeItem->setVariables(array_merge($routeItem->getVariables(), $this->vars));

		return $routeItem;
	}

	/**
	 * matchSegment
	 *
	 * @param array $segments
	 * @param array $node
	 * @param int   $level
	 *
	 * @return  bool|Route
	 */
	protected function matchSegment($segments, $node, $level = 1)
	{
		$segment = isset($segments[$level - 1]) ? $segments[$level - 1] : false;

		if ($segment === false)
		{
			return false;
		}

		$segment = $segment ? : '/';

		foreach ($node as $regex => $child)
		{
			$this->count++;

			// Start with a '(' is a regex
			if ($regex[0] == '(')
			{
				preg_match(chr(1) . $regex . chr(1), $segment, $match);

				if (!$match)
				{
					continue;
				}

				RouteHelper::getVariables($match, $this->vars);
			}
			// Otherwise it is a static string
			else
			{
				if ($regex != $segment)
				{
					continue;
				}
			}

			$result = false;

			// Has child, iterate it.
			if ($child && is_array($child))
			{
				$child = $this->matchSegment($segments, $child, $level + 1);
			}

			// If is string, means we get a route index, using this index to find Route from maps.
			if (is_string($child))
			{
				$child = $this->routes[$this->routeMaps[$child]];
			}

			// Match this Route
			if ($child instanceof Route)
			{
				$result = $this->matchOptions($child, $this->method, $this->options);
			}

			// If match fail, continue find next element.
			if (!$result)
			{
				continue;
			}

			return $child;
		}

		return false;
	}

	/**
	 * buildTree
	 *
	 * @param bool $refresh
	 *
	 * @return  static
	 */
	protected function buildTree($refresh = false)
	{
		if ($this->tree && $this->debug && $refresh)
		{
			return $this;
		}

		// Build Tree
		foreach ($this->routes as $routeItem)
		{
			$pattern = $routeItem->getPattern();

			// Compile this route
			$regex = TrieCompiler::compile($pattern, $routeItem->getRequirements());

			$regex = trim($regex, chr(1) . '^$');

			// Make sure no other '/' is the path separator
			$regex = str_replace('[^/]', '{:PLACEHOLDER:}', $regex);

			// Split it
			$regex = explode('/', $regex);

			// Start build tree
			$node = &$this->tree;

			$length = count($regex);

			foreach ((array) $regex as $k => $segment)
			{
				// Fallback the placeholder to /
				$segment = str_replace('{:PLACEHOLDER:}', '[^/]', $segment);

				$segment = $segment ? : '/';

				if (!isset($node[$segment]))
				{
					$node[$segment] = array();
				}

				// If is last segment, set it as Route name,
				// that we can search it later or cache this map.
				if ($k + 1 == $length)
				{
					$node[$segment] = $routeItem->getName();
				}

				$node = &$node[$segment];
			}
		}

		return $this;
	}

	/**
	 * clear
	 *
	 * @return  void
	 */
	public function clear()
	{
		$this->tree = array();
	}

	/**
	 * Method to get property Tree
	 *
	 * @return  array
	 */
	public function getTree()
	{
		return $this->tree;
	}

	/**
	 * Method to set property tree
	 *
	 * @param   array $tree
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setTree($tree)
	{
		$this->tree = $tree;

		return $this;
	}
}
