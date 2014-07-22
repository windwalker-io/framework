<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Router;

/**
 * Class Route
 *
 * @since 1.0
 */
class Route
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Property pattern.
	 *
	 * @var  string
	 */
	protected $pattern = null;

	/**
	 * Property regex.
	 *
	 * @var  string
	 */
	protected $regex = null;

	/**
	 * Property vars.
	 *
	 * @var  array
	 */
	protected $vars = array();

	/**
	 * Property allowMethods.
	 *
	 * @var  array
	 */
	protected $allowMethods = array();

	/**
	 * Property method.
	 *
	 * @var  string
	 */
	protected $method = array();

	/**
	 * Property variables.
	 *
	 * @var  string
	 */
	protected $variables = array();

	/**
	 * Class init.
	 *
	 * @param string       $pattern
	 * @param array        $variables
	 * @param array|string $allowMethods
	 */
	public function __construct($pattern, $variables, $allowMethods = array())
	{
		$this->pattern = $pattern;
		$this->variables = $variables;

		$this->setAllowMethods($allowMethods);
	}

	/**
	 * compile
	 *
	 * @return  Route
	 */
	public function compile()
	{
		// If already compiled, just return.
		if ($this->regex)
		{
			return $this;
		}

		// Sanitize and explode the pattern.
		$pattern = explode('/', trim(parse_url((string) $this->pattern, PHP_URL_PATH), ' /'));

		// Prepare the route variables
		$vars = array();

		// Initialize regular expression
		$regex = array();

		// Loop on each segment
		foreach ($pattern as $segment)
		{
			if ($segment == '')
			{
				// Match root route.
				$regex[] = '\\/';
			}
			elseif ($segment == '*')
			{
				// Match a splat with no variable.
				$regex[] = '.*';
			}
			elseif ($segment[0] == '*')
			{
				// Match a splat and capture the data to a named variable.
				$vars[] = substr($segment, 1);
				$regex[] = '(.*)';
			}
			elseif ($segment[0] == '\\' && $segment[1] == '*')
			{
				// Match an escaped splat segment.
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
			{
				// Match an unnamed variable without capture.
				$regex[] = '[^/]*';
			}
			elseif ($segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$vars[] = substr($segment, 1);
				$regex[] = '([^/]*)';
			}
			elseif ($segment[0] == '\\' && $segment[1] == ':')
			{
				// Match a segment with an escaped variable character prefix.
				$regex[] = preg_quote(substr($segment, 1));
			}
			else
			{
				// Match the standard segment.
				$regex[] = preg_quote($segment);
			}
		}

		$this->regex = chr(1) . '^' . implode('/', $regex) . '$' . chr(1);

		$this->vars = $vars;

		return $this;
	}

	/**
	 * match
	 *
	 * @param string $route
	 *
	 * @return  boolean|array
	 */
	public function match($route)
	{
		$this->compile();

		if ($this->allowMethods && !in_array(strtoupper($this->method), $this->allowMethods))
		{
			return false;
		}

		$variables = array();

		if (preg_match($this->regex, $route, $matches))
		{
			foreach ($this->vars as $i => $var)
			{
				$variables[$var] = $matches[$i + 1];
			}

			$variables['_rawRoute'] = $route;
		}
		else
		{
			return false;
		}

		return $this->variables = array_merge($this->variables, $variables);
	}

	/**
	 * Build route.
	 *
	 * @param array  $queries Http queries.
	 *
	 * @return  string
	 */
	public function build($queries = array())
	{
		if (empty($this->pattern))
		{
			return array();
		}

		$this->compile();

		/* TODO: Implement Build Handler
		if (is_callable($this->buildHandler[$name]))
		{
			call_user_func_array($this->buildHandler[$name], array($queries));
		}
		*/

		$replace = array();

		$pattern = $this->pattern;

		// TODO: Need rewrite build logic.
		foreach ($this->vars as $key)
		{
			$var = isset($queries[$key]) ? $queries[$key] : 'null';

			if (is_array($var) || is_object($var))
			{
				$var = implode('/', (array) $var);

				$key2 = '*' . $key;

				$replace[$key2] = $var;
			}
			else
			{
				$key2 = ':' . $key;

				$replace[$key2] = $var;
			}

			if (strpos($pattern, $key2) !== false)
			{
				unset($queries[$key]);
			}
		}

		$pattern = strtr($pattern, $replace);

		return $pattern;
	}

	/**
	 * getPattern
	 *
	 * @return  string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * setPattern
	 *
	 * @param   string $pattern
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setPattern($pattern)
	{
		$this->pattern = $pattern;

		return $this;
	}

	/**
	 * getRegex
	 *
	 * @return  string
	 */
	public function getRegex()
	{
		return $this->regex;
	}

	/**
	 * setRegex
	 *
	 * @param   string $regex
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setRegex($regex)
	{
		$this->regex = $regex;

		return $this;
	}

	/**
	 * getVars
	 *
	 * @return  array
	 */
	public function getVars()
	{
		return $this->vars;
	}

	/**
	 * setVars
	 *
	 * @param   array $vars
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setVars($vars)
	{
		$this->vars = $vars;

		return $this;
	}

	/**
	 * getMethod
	 *
	 * @return  string
	 */
	public function getAllowMethods()
	{
		return $this->allowMethods;
	}

	/**
	 * setMethod
	 *
	 * @param   array|string $methods
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setAllowMethods($methods)
	{
		$methods = (array) $methods;

		$methods = array_map('strtoupper', $methods);

		$this->allowMethods = $methods;

		return $this;
	}

	/**
	 * getVariables
	 *
	 * @return  string
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * setVariables
	 *
	 * @param   string $variables
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setVariables($variables)
	{
		$this->variables = $variables;

		return $this;
	}

	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * setName
	 *
	 * @param   string $name
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * getMethod
	 *
	 * @return  string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * setMethod
	 *
	 * @param   string $method
	 *
	 * @return  Route  Return self to support chaining.
	 */
	public function setMethod($method)
	{
		$this->method = $method;

		return $this;
	}
}
