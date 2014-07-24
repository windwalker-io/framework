<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Input;

use Joomla\Filter\InputFilter;

/**
 * Class Input
 *
 * @since 1.0
 */
class Input
{
	/**
	 * Filter object to use.
	 *
	 * @var    \Joomla\Filter\InputFilter
	 * @since  1.0
	 */
	protected $filter = null;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $data = array();

	/**
	 * Input objects
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $inputs = array();

	/**
	 * Constructor.
	 *
	 * @param   array       $source Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   InputFilter $filter The input filter object.
	 *
	 * @since   1.0
	 */
	public function __construct($source = null, InputFilter $filter = null)
	{
		$this->filter = $filter ? : new InputFilter;

		if (is_null($source))
		{
			$this->data = &$_REQUEST;
		}
		else
		{
			$this->data = $source;
		}
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  Input  The request input object
	 *
	 * @since   1.0
	 */
	public function __get($name)
	{
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = __NAMESPACE__ . ucfirst($name);

		if (class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->filter);

			return $this->inputs[$name];
		}

		$superGlobal = '_' . strtoupper($name);

		if (isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new Input($GLOBALS[$superGlobal], $this->filter);

			return $this->inputs[$name];
		}

		// TODO throw an exception
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 *
	 * @since   1.0
	 * @see     Countable::count()
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   1.0
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			return $this->filter->clean($this->data[$name], $filter);
		}

		return $default;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 *                              If empty and datasource is null, all the input data will be returned
	 *                              but filtered using the default case in JFilterInput::clean.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   1.0
	 */
	public function getArray(array $vars = array(), $datasource = null)
	{
		if (empty($vars) && is_null($datasource))
		{
			$vars = $this->data;
		}

		$results = array();

		foreach ($vars as $k => $v)
		{
			if (is_array($v))
			{
				if (is_null($datasource))
				{
					$results[$k] = $this->getArray($v, $this->get($k, null, 'array'));
				}
				else
				{
					$results[$k] = $this->getArray($v, $datasource[$k]);
				}
			}
			else
			{
				if (is_null($datasource))
				{
					$results[$k] = $this->get($k, null, $v);
				}
				elseif (isset($datasource[$k]))
				{
					$results[$k] = $this->filter->clean($datasource[$k], $v);
				}
				else
				{
					$results[$k] = $this->filter->clean(null, $v);
				}
			}
		}

		return $results;
	}

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Define a value. The value will only be set if there's no value for the name or if it is null.
	 *
	 * @param   string  $name   Name of the value to define.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function def($name, $value)
	{
		if (isset($this->data[$name]))
		{
			return;
		}

		$this->data[$name] = $value;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 *
	 * @since   1.0
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;

			if (isset($arguments[1]))
			{
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string   The request method.
	 *
	 * @since   1.0
	 */
	public function getMethod()
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD']);

		return $method;
	}

	/**
	 * Method to serialize the input.
	 *
	 * @return  string  The serialized input.
	 *
	 * @since   1.0
	 */
	public function serialize()
	{
		// Load all of the inputs.
		$this->loadAllInputs();

		// Remove $_ENV and $_SERVER from the inputs.
		$inputs = $this->inputs;
		unset($inputs['env']);
		unset($inputs['server']);

		// Serialize the options, data, and inputs.
		return serialize(array($this->options, $this->data, $inputs));
	}

	/**
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  Input  The input object.
	 *
	 * @since   1.0
	 */
	public function unserialize($input)
	{
		// Unserialize the options, data, and inputs.
		list($this->options, $this->data, $this->inputs) = unserialize($input);

		// Load the filter.
		if (isset($this->options['filter']))
		{
			$this->filter = $this->options['filter'];
		}
		else
		{
			$this->filter = new \Joomla\Filter\InputFilter;
		}
	}

	/**
	 * Method to load all of the global inputs.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadAllInputs()
	{
		static $loaded = false;

		if (!$loaded)
		{
			// Load up all the globals.
			foreach ($GLOBALS as $global => $data)
			{
				// Check if the global starts with an underscore.
				if (strpos($global, '_') === 0)
				{
					// Convert global name to input name.
					$global = strtolower($global);
					$global = substr($global, 1);

					// Get the input.
					$this->$global;
				}
			}

			$loaded = true;
		}
	}
}
 