<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Filter;

use Windwalker\Filter\Type\AbstractFilterType;

/**
 * Class Filter
 *
 * @since {DEPLOY_VERSION}
 */
class InputFilter
{
	const INTEGER = 'INTEGER';
	const UINT = 'UINT';
	const FLOAT = 'FLOAT';
	const BOOLEAN = 'BOOLEAN';
	const WORD = 'WORD';
	const ALNUM = 'ALNUM';
	const CMD = 'CMD';
	const BASE64 = 'BASE64';
	const STRING = 'STRING';
	const HTML = 'HTML';
	const ARRAY_TYPE = 'ARRAY';
	const PATH = 'PATH';
	const USERNAME = 'USERNAME';
	const RAW = 'RAW';

	/**
	 * Property handlers.
	 *
	 * @var  callable[]
	 */
	protected $handlers = array();

	/**
	 * Property unknownHandler.
	 *
	 * @var  callable
	 */
	protected $defaultHandler = null;

	/**
	 * Property htmlCleaner.
	 *
	 * @var  HtmlCleaner
	 */
	protected $htmlCleaner = null;

	/**
	 * Class init.
	 *
	 * @param $htmlCleaner
	 */
	public function __construct(HtmlCleaner $htmlCleaner = null)
	{
		$this->htmlCleaner = $htmlCleaner ? : new HtmlCleaner;
	}

	/**
	 * clean
	 *
	 * @param string                 $source
	 * @param string|callable|object $filter
	 *
	 * @return  mixed
	 */
	public function clean($source, $filter = 'string')
	{
		// Find handler to filter this text
		if (is_callable($filter))
		{
			return $filter($source);
		}
		elseif (!empty($this->handlers[$filter]) && is_callable($this->handlers[$filter]))
		{
			return $this->handlers[$filter]($source);
		}

		// Use default handler
		if (is_callable($this->defaultHandler))
		{
			$defaultFilter = $this->defaultHandler;

			return $defaultFilter($source);
		}

		// No any filter matched, return source.
		return $source;
	}

	/**
	 * getHandlers
	 *
	 * @param string $name
	 *
	 * @return  \callable
	 */
	public function getHandler($name)
	{
		return $this->handlers[$name];
	}

	/**
	 * setHandlers
	 *
	 * @param   string      $name
	 * @param   \callable[] $handler
	 *
	 * @throws  \InvalidArgumentException
	 * @return  Filter  Return self to support chaining.
	 */
	public function setHandler($name, $handler)
	{
		if (is_object($handler) && !($handler instanceof AbstractFilterType) && !($handler instanceof \Closure))
		{
			throw new \InvalidArgumentException('Object filter handler should extends AbstractFilterType or be a Closure.');
		}

		$this->handlers[strtoupper($name)] = $handler;

		return $this;
	}

	/**
	 * gethtmlCleaner
	 *
	 * @return  \Windwalker\Filter\HtmlCleaner
	 */
	public function getHtmlCleaner()
	{
		return $this->htmlCleaner;
	}

	/**
	 * sethtmlCleaner
	 *
	 * @param   \Windwalker\Filter\HtmlCleaner $htmlCleaner
	 *
	 * @return  Filter  Return self to support chaining.
	 */
	public function setHtmlCleaner($htmlCleaner)
	{
		$this->htmlCleaner = $htmlCleaner;

		return $this;
	}

	/**
	 * getDefaultHandler
	 *
	 * @return  callable
	 */
	public function getDefaultHandler()
	{
		return $this->defaultHandler;
	}

	/**
	 * setDefaultHandler
	 *
	 * @param   callable $defaultHandler
	 *
	 * @return  Filter  Return self to support chaining.
	 */
	public function setDefaultHandler($defaultHandler)
	{
		$this->defaultHandler = $defaultHandler;

		return $this;
	}

	/**
	 * loadDefaultHandlers
	 *
	 * @return  void
	 */
	protected function loadDefaultHandlers()
	{
		$filter = $this->htmlCleaner;

		// INT / INTEGER
		$this->handlers[static::INTEGER] = $this->handlers['INT'] = function($source)
		{
			// Only use the first integer value
			preg_match('/-?[0-9]+/', (string) $source, $matches);

			return isset($matches[0]) ? abs((int) $matches[0]) : null;
		};

		// UINT
		$this->handlers[static::UINT] = function($source)
		{
			// Only use the first integer value
			preg_match('/-?[0-9]+/', (string) $source, $matches);

			return isset($matches[0]) ? abs((int) $matches[0]) : null;
		};

		// FLOAT / DOUBLE
		$this->handlers[static::FLOAT] = $this->handlers['DOUBLE'] = function($source)
		{
			// Only use the first floating point value
			preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);

			return isset($matches[0]) ? (float) $matches[0] : null;
		};

		// BOOLEAN / BOOL
		$this->handlers[static::BOOLEAN] = $this->handlers['BOOL'] = function($source)
		{
			return (bool) $source;
		};

		// WORD
		$this->handlers[static::WORD] = function($source)
		{
			return (string) preg_replace('/[^A-Z_]/i', '', $source);
		};

		// ALNUM
		$this->handlers[static::ALNUM] = function($source)
		{
			return (string) preg_replace('/[^A-Z0-9]/i', '', $source);
		};

		// CMD
		$this->handlers[static::UINT] = function($source)
		{
			$result = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $source);

			return ltrim($result, '.');
		};

		// BASE64
		$this->handlers[static::BASE64] = function($source)
		{
			return (string) preg_replace('/[^A-Z0-9\/+=]/i', '', $source);
		};

		// STRING
		$this->handlers[static::STRING] = function($source) use ($filter)
		{
			return (string) $filter->remove($filter->decode((string) $source));
		};

		// HTML
		$this->handlers[static::HTML] = function($source) use ($filter)
		{
			return (string) $filter->remove((string) $source);
		};

		// ARRAY
		$this->handlers[static::ARRAY_TYPE] = function($source)
		{
			return (array) $source;
		};

		// PATH
		$this->handlers[static::PATH] = function($source)
		{
			$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
			preg_match($pattern, (string) $source, $matches);

			return isset($matches[0]) ? (string) $matches[0] : null;
		};

		// USERNAME
		$this->handlers[static::USERNAME] = function($source)
		{
			return (string) preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $source);
		};

		// RAW
		$this->handlers[static::RAW] = function($source)
		{
			return $source;
		};

		// UNKNOWN
		$this->defaultHandler = function($source) use ($filter)
		{
			// Are we dealing with an array?
			if (is_array($source))
			{
				foreach ($source as $key => $value)
				{
					// Filter element for XSS and other 'bad' code etc.
					if (is_string($value))
					{
						$source[$key] = $filter->remove($filter->decode($value));
					}
				}

				return $source;
			}
			else
			{
				// Or a string?
				if (is_string($source) && !empty($source))
				{
					// Filter source for XSS and other 'bad' code etc.
					return $filter->remove($filter->decode($source));
				}
				else
				{
					// Not an array or string.. return the passed parameter
					return $source;
				}
			}
		};
	}
}

