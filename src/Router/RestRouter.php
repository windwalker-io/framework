<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router;

/**
 * RESTful Web application router class.
 */
class RestRouter extends SingleActionRouter
{
	/**
	 * An array of HTTP Method => controller suffix pairs for routing the request.
	 *
	 * @var  array
	 */
	protected $suffixMap = array(
		'GET' => 'Get',
		'POST' => 'Create',
		'PUT' => 'Update',
		'PATCH' => 'Update',
		'DELETE' => 'Delete',
		'HEAD' => 'Head',
		'OPTIONS' => 'Options'
	);

	/**
	 * Property customMethod.
	 *
	 * @var  string
	 */
	protected $customMethod = null;

	/**
	 * A boolean allowing to pass _method as parameter in POST requests
	 *
	 * @var  boolean
	 */
	protected $allowCustomMethod = false;

	/**
	 * Get the property to allow or not method in POST request
	 *
	 * @return  boolean
	 */
	public function isAllowCustomMethod()
	{
		return $this->allowCustomMethod;
	}

	/**
	 * Set a controller class suffix for a given HTTP method.
	 *
	 * @param   string  $method  The HTTP method for which to set the class suffix.
	 * @param   string  $suffix  The class suffix to use when fetching the controller name for a given request.
	 *
	 * @return  static  Returns itself to support chaining.
	 */
	public function setHttpMethodSuffix($method, $suffix)
	{
		$this->suffixMap[strtoupper((string) $method)] = (string) $suffix;

		return $this;
	}

	/**
	 * Set to allow or not method in POST request
	 *
	 * @param   boolean  $value  A boolean to allow or not method in POST request
	 *
	 * @return  static
	 */
	public function allowCustomMethod($value)
	{
		$this->allowCustomMethod = $value;

		return $this;
	}

	/**
	 * getCustomMethod
	 *
	 * @return  string
	 */
	public function getCustomMethod()
	{
		return $this->customMethod;
	}

	/**
	 * setCustomMethod
	 *
	 * @param   string $customMethod
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCustomMethod($customMethod)
	{
		$this->customMethod = strtoupper($customMethod);

		return $this;
	}

	/**
	 * Get the controller class suffix string.
	 *
	 * @param string $method
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	protected function fetchControllerSuffix($method = 'GET')
	{
		$method = strtoupper($method);

		// Validate that we have a map to handle the given HTTP method.
		if (!isset($this->suffixMap[$method]))
		{
			throw new \RuntimeException(sprintf('Unable to support the HTTP method `%s`.', $method), 404);
		}

		// Check if request method is POST
		if ( $this->allowCustomMethod == true && strcmp(strtoupper($method), 'POST') === 0)
		{
			// Get the method from input
			$postMethod = $this->getCustomMethod();

			// Validate that we have a map to handle the given HTTP method from input
			if ($postMethod && isset($this->suffixMap[strtoupper($postMethod)]))
			{
				return ucfirst($this->suffixMap[strtoupper($postMethod)]);
			}
		}

		return ucfirst($this->suffixMap[$method]);
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route   The route string for which to find and execute a controller.
	 * @param   string  $method
	 * @param   array   $options
	 *
	 * @return  string  The controller name for the given route excluding prefix.
	 *
	 * @since   2.0
	 */
	public function match($route, $method = 'GET', $options = array())
	{
		$name = parent::match($route, $method, $options);

		// Append the HTTP method based suffix.
		$name .= $this->fetchControllerSuffix($method);

		return $name;
	}
}
