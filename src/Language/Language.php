<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language;

use Windwalker\Language\Format\FormatInterface;
use Windwalker\Language\Format\IniFormat;
use Windwalker\Language\Loader\FileLoader;
use Windwalker\Language\Loader\LoaderInterface;
use Windwalker\Language\Localise\LocaliseInterface;

/**
 * Class Language
 *
 * @since 2.0
 */
class Language implements LanguageInterface
{
	/**
	 * Property strings.
	 *
	 * @var  string[]
	 */
	protected $strings = array();

	/**
	 * Property used.
	 *
	 * @var  string[]
	 */
	protected $used = array();

	/**
	 * Property orphans.
	 *
	 * @var  string[]
	 */
	protected $orphans = array();

	/**
	 * Property debug.
	 *
	 * @var  boolean
	 */
	protected $debug = false;

	/**
	 * Property loader.
	 *
	 * @var  LoaderInterface[]
	 */
	protected $loaders = array();

	/**
	 * Property format.
	 *
	 * @var  FormatInterface[]
	 */
	protected $formats = array();

	/**
	 * Property locale.
	 *
	 * @var string
	 */
	protected $locale = null;

	/**
	 * Property defaultLocale.
	 *
	 * @var  string
	 */
	protected $defaultLocale = null;

	/**
	 * Property localise.
	 *
	 * @var  LocaliseInterface[]
	 */
	protected $localises = array();

	/**
	 * Property normalizeHandler.
	 *
	 * @var  callable
	 */
	protected $normalizeHandler = array('Windwalker\\Language\\LanguageNormalize', 'toLanguageKey');

	/**
	 * Constructor.
	 *
	 * @param string                            $locale
	 * @param string                            $defaultLocale
	 * @param LoaderInterface|LoaderInterface[] $loaders
	 * @param FormatInterface|FormatInterface[] $formats
	 */
	public function __construct($locale = 'en-GB', $defaultLocale = 'en-GB', $loaders = null, $formats = null)
	{
		$formats = $formats ? : new IniFormat;
		$loaders = $loaders ? : new FileLoader;

		$this->setLocale($locale);
		$this->setDefaultLocale($locale);
		$this->setFormats($formats);
		$this->setLoaders($loaders);
	}

	/**
	 * load
	 *
	 * @param string $file
	 * @param string $format
	 * @param string $loader
	 *
	 * @return  $this
	 */
	public function load($file, $format = 'ini', $loader = 'file')
	{
		if ($format == 'php')
		{
			$loader = 'php';
		}

		$string = $this->getLoader($loader)->load($file);

		$string = $this->getFormat($format)->parse($string);

		$this->addStrings($string);

		return $this;
	}

	/**
	 * translate
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function translate($key)
	{
		$normalizeKey = $this->normalize($key);

		if ($this->exists($normalizeKey, false))
		{
			$string = $this->strings[$normalizeKey];

			// In debug mode, we notice user this is a translated string.
			if ($this->debug)
			{
				$string = '**' . $string . '**';
			}

			// Store used keys
			if (!in_array($normalizeKey, $this->used))
			{
				$this->used[] = $normalizeKey;
			}

			return $string;
		}

		// In debug mode, we notice user this is a translating string but not found.
		if ($this->debug)
		{
			$this->orphans[] = $normalizeKey;

			$key = '??' . $key . '??';
		}

		return $key;
	}

	/**
	 * plural
	 *
	 * @param string $string
	 * @param int    $count
	 *
	 * @return  string
	 */
	public function plural($string, $count = 1)
	{
		$localise = $this->getLocalise($this->locale);

		// Get language plural handles
		$suffix = $localise->getPluralSuffix((int) $count);

		if ($suffix || $suffix == 0)
		{
			$key = $string . '_' . $suffix;

			if ($this->exists($key))
			{
				$string = $key;
			}
		}

		// If current locale do not have singular & plural string
		// We try to do same thing to default locale
		if (!$this->exists($string))
		{
			// Find default localise
			$localise = $this->getLocalise($this->defaultLocale);

			// Get language plural handles
			$suffix = $localise->getPluralSuffix((int) $count);

			if ($suffix || $suffix == 0)
			{
				$key = $string . '_' . $suffix;

				if ($this->exists($key))
				{
					$string = $key;
				}
			}
		}

		// If more arguments exists, use sprintf()
		$args = func_get_args();

		unset($args[1]);

		if (count($args))
		{
			$args[0] = $string;

			return call_user_func_array(array($this, 'sprintf'), $args);
		}

		// Fallback to default translate
		return $this->translate($string);
	}

	/**
	 * sprintf
	 *
	 * @param string $key
	 *
	 * @return  mixed
	 */
	public function sprintf($key)
	{
		$args = func_get_args();

		$args[0] = $this->translate($key);

		return call_user_func_array('sprintf', $args);
	}

	/**
	 * exists
	 *
	 * @param string $key
	 * @param bool   $normalize
	 *
	 * @return  boolean
	 */
	public function exists($key, $normalize = true)
	{
		if ($normalize)
		{
			$key = $this->normalize($key);
		}

		return isset($this->strings[$key]);
	}

	/**
	 * addString
	 *
	 * @param string $key
	 * @param string $string
	 *
	 * @return  $this
	 */
	public function addString($key, $string)
	{
		$this->strings[$this->normalize($key)] = $string;

		return $this;
	}

	/**
	 * addStrings
	 *
	 * @param string[] $strings
	 *
	 * @return  $this
	 */
	public function addStrings($strings)
	{
		foreach ($strings as $key => $string)
		{
			$this->addString($key, $string);
		}

		return $this;
	}

	/**
	 * setDebug
	 *
	 * @param   boolean $debug
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;

		return $this;
	}

	/**
	 * getLoader
	 *
	 * @param string $name
	 *
	 * @throws  \DomainException
	 * @return  LoaderInterface
	 */
	public function getLoader($name)
	{
		if (empty($this->loaders[$name]) || !($this->loaders[$name] instanceof LoaderInterface))
		{
			$class = sprintf('Windwalker\\Language\\Loader\\%sLoader', ucfirst($name));

			if (!class_exists($class))
			{
				throw new \DomainException('Format ' . $name . ' not support. Class: ' . $class . ' not found');
			}

			$this->loaders[$name] = new $class;
		}

		return $this->loaders[$name];
	}

	/**
	 * setLoader
	 *
	 * @param   string          $name
	 * @param   LoaderInterface $loader
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setLoader($name, LoaderInterface $loader)
	{
		// If name is int or null, get name from Loader object.
		$name = (is_numeric($name) || !$name) ? $loader->getName() : $name;

		$this->loaders[$name] = $loader;

		return $this;
	}

	/**
	 * setLoaders
	 *
	 * @param LoaderInterface|LoaderInterface[] $loaders
	 *
	 * @return  void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setLoaders($loaders)
	{
		if ($loaders instanceof LoaderInterface)
		{
			$loaders = array($loaders);
		}

		foreach ($loaders as $name => $loader)
		{
			$this->setLoader($name, $loader);
		}
	}

	/**
	 * getFormat
	 *
	 * @param string $name
	 *
	 * @throws \DomainException
	 * @return  FormatInterface
	 */
	public function getFormat($name)
	{
		if (empty($this->formats[$name]) || !($this->formats[$name] instanceof FormatInterface))
		{
			$class = sprintf('Windwalker\\Language\\Format\\%sFormat', $name);

			if (!class_exists($class))
			{
				throw new \DomainException('Format ' . $name . ' not support. Class: ' . $class . ' not found');
			}

			$this->formats[$name] = new $class;
		}

		return $this->formats[$name];
	}

	/**
	 * setFormat
	 *
	 * @param   string          $name
	 * @param   FormatInterface $format
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setFormat($name, FormatInterface $format)
	{
		// If name is int or null, get name from Format object.
		$name = (is_numeric($name) || !$name) ? $format->getName() : $name;

		$this->formats[$name] = $format;

		return $this;
	}

	/**
	 * setFormats
	 *
	 * @param FormatInterface|FormatInterface[] $formats
	 *
	 * @return  void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setFormats($formats)
	{
		if ($formats instanceof FormatInterface)
		{
			$formats = array($formats);
		}

		foreach ($formats as $name => $format)
		{
			$this->setFormat($name, $format);
		}
	}

	/**
	 * getOrphans
	 *
	 * @return  \string[]
	 */
	public function getOrphans()
	{
		return $this->orphans;
	}

	/**
	 * getUsed
	 *
	 * @return  \string[]
	 */
	public function getUsed()
	{
		return $this->used;
	}

	/**
	 * getLocale
	 *
	 * @return  string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * setLocale
	 *
	 * @param   string $locale
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setLocale($locale)
	{
		$this->locale = LanguageNormalize::toLanguageTag($locale);

		$this->localises[$locale] = null;

		return $this;
	}

	/**
	 * Method to get property DefaultLocale
	 *
	 * @return  string
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}

	/**
	 * Method to set property defaultLocale
	 *
	 * @param   string $defaultLocale
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDefaultLocale($defaultLocale)
	{
		$this->defaultLocale = LanguageNormalize::toLanguageTag($defaultLocale);

		return $this;
	}

	/**
	 * setLocalise
	 *
	 * @param   string            $locale
	 * @param   LocaliseInterface $localise
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setLocalise($locale = 'en-GB', LocaliseInterface $localise)
	{
		$locale = LanguageNormalize::toLanguageTag($locale);

		$this->localises[$locale] = $localise;

		return $this;
	}

	/**
	 * getLocalise
	 *
	 * @param string $locale
	 *
	 * @return  LocaliseInterface
	 */
	protected function getLocalise($locale = 'en-GB')
	{
		$locale = LanguageNormalize::toLanguageTag($locale);

		if (empty($this->localises[$locale]) || !($this->localises[$locale] instanceof LocaliseInterface))
		{
			$tag = LanguageNormalize::getLocaliseClassPrefix($this->locale);

			$class = sprintf('Windwalker\\Language\\Localise\\%sLocalise', $tag);

			if (!class_exists($class))
			{
				$class = 'Windwalker\\Language\\Localise\\NullLocalise';
			}

			$this->localises[$locale] = new $class;
		}

		return $this->localises[$locale];
	}

	/**
	 * normalize
	 *
	 * @param string $string
	 *
	 * @throws \UnexpectedValueException
	 * @return  mixed
	 */
	public function normalize($string)
	{
		$handler = $this->getNormalizeHandler();

		if (!is_callable($handler))
		{
			throw new \UnexpectedValueException('Normalize handler is not callable.');
		}

		return call_user_func($handler, $string);
	}

	/**
	 * getNormalizeHandler
	 *
	 * @return  callable
	 */
	public function getNormalizeHandler()
	{
		return $this->normalizeHandler;
	}

	/**
	 * setNormalizeHandler
	 *
	 * @param   callable $normalizeHandler
	 *
	 * @return  Language  Return self to support chaining.
	 */
	public function setNormalizeHandler($normalizeHandler)
	{
		$this->normalizeHandler = $normalizeHandler;

		return $this;
	}
}

