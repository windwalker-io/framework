<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router;

/**
 * Class Route
 *
 * @since 2.0
 */
class Route implements \IteratorAggregate
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
	 * Property allowMethods.
	 *
	 * @var  array
	 */
	protected $allowMethods = [];

	/**
	 * Property variables.
	 *
	 * @var  array
	 */
	protected $variables = [];

	/**
	 * Property requirements.
	 *
	 * @var  array
	 */
	public $requirements = [];

	/**
	 * Property host.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Property scheme.
	 *
	 * @var  string
	 */
	protected $scheme = '';

	/**
	 * Property port.
	 *
	 * @var integer
	 */
	protected $port;

	/**
	 * Property sslPort.
	 *
	 * @var integer
	 */
	protected $sslPort;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = [];

	/**
	 * Property ssl.
	 *
	 * @var boolean
	 */
	protected $ssl = false;

	/**
	 * Property extra.
	 *
	 * @var  array
	 */
	protected $extra = [];

	/**
	 * Class init.
	 *
	 * @param string       $name
	 * @param string       $pattern
	 * @param array        $variables
	 * @param array|string $allowMethods
	 * @param array        $options
	 */
	public function __construct($name, $pattern, $variables = [], $allowMethods = [], $options = [])
	{
		$this->name = $name;
		$this->variables = $variables;

		$this->setPattern($pattern);
		$this->setOptions($options);
		$this->setAllowMethods($allowMethods);
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
		$this->pattern = RouteHelper::normalise($pattern);

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
	 * @return  array
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * setVariables
	 *
	 * @param   array $variables
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
	 * Method to get property Options
	 *
	 * @return  array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		$options = $this->prepareOptions($options);

		$this->setHost($options['host']);
		$this->setScheme($options['scheme']);
		$this->setPort($options['port']);
		$this->setSslPort($options['sslPort']);
		$this->setRequirements($options['requirements']);
		$this->setExtraValues($options['extra']);

		return $this;
	}

	/**
	 * prepareOptions
	 *
	 * @param   array $options
	 *
	 * @return  array
	 */
	public function prepareOptions($options)
	{
		$defaultOptions = [
			'requirements' => [],
			'options' => [],
			'host' => null,
			'scheme' => null,
			'port' => null,
			'sslPort' => null,
			'extra' => []
		];

		return array_merge($defaultOptions, (array) $options);
	}

	/**
	 * Method to get property Options
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		if (array_key_exists($name, $this->options))
		{
			return $this->options[$name];
		}

		return $default;
	}

	/**
	 * Method to set property options
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Method to get property SslPort
	 *
	 * @return  int
	 */
	public function getSslPort()
	{
		return $this->sslPort;
	}

	/**
	 * Method to set property sslPort
	 *
	 * @param   int $sslPort
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSslPort($sslPort)
	{
		$this->sslPort = (int) $sslPort;

		return $this;
	}

	/**
	 * Method to get property Port
	 *
	 * @return  int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Method to set property port
	 *
	 * @param   int $port
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPort($port)
	{
		$this->port = (int) $port;

		return $this;
	}

	/**
	 * Method to get property Scheme
	 *
	 * @return  string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Method to set property scheme
	 *
	 * @param   string $scheme
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setScheme($scheme)
	{
		$this->scheme = strtolower($scheme);

		$this->ssl = ($this->scheme == 'https');

		return $this;
	}

	/**
	 * Method to get property Host
	 *
	 * @return  string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Method to set property host
	 *
	 * @param   string $host
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setHost($host)
	{
		$this->host = strtolower($host);

		return $this;
	}

	/**
	 * Method to get property Requirements
	 *
	 * @return  array
	 */
	public function getRequirements()
	{
		return $this->requirements;
	}

	/**
	 * Method to set property requirements
	 *
	 * @param   array $requirements
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRequirements($requirements)
	{
		$this->requirements = (array) $requirements;

		return $this;
	}

	/**
	 * Method to get property Ssl
	 *
	 * @return  boolean
	 */
	public function getSSL()
	{
		return $this->ssl;
	}

	/**
	 * Method to set property ssl
	 *
	 * @param   boolean $ssl
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSSL($ssl)
	{
		$this->ssl = $ssl;

		return $this;
	}

	/**
	 * Method to get property Extra
	 *
	 * @return  array
	 */
	public function getExtraValues()
	{
		return $this->extra;
	}

	/**
	 * Method to set property extra
	 *
	 * @param   array $extra
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setExtraValues(array $extra)
	{
		$this->extra = $extra;

		return $this;
	}

	/**
	 * getExtra
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getExtra($name, $default = null)
	{
		if (isset($this->extra[$name]))
		{
			return $this->extra[$name];
		}

		return $default;
	}

	/**
	 * setExtra
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  static
	 */
	public function setExtra($name, $value)
	{
		$this->extra[$name] = $value;

		return $this;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return \Traversable An instance of an object implementing Iterator or Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator(get_object_vars($this));
	}

	/**
	 * getAllData
	 *
	 * @return  array
	 */
	public function getAllData()
	{
		return get_object_vars($this);
	}
}
