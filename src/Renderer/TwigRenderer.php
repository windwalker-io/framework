<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Renderer;

use Windwalker\Registry\Registry;
use Windwalker\Renderer\Twig\GlobalContainer;

/**
 * Class PhpRenderer
 *
 * @since 2.0
 */
class TwigRenderer extends AbstractAdapterRenderer
{
	/**
	 * Property twig.
	 *
	 * @var  \Twig_environment
	 */
	protected $engine = null;

	/**
	 * Property loader.
	 *
	 * @var  \Twig_Loader_Filesystem
	 */
	protected $loader = null;

	/**
	 * Property extensions.
	 *
	 * @var  array
	 */
	protected $extensions = array();

	/**
	 * Property config.
	 *
	 * @var  Registry|array
	 */
	protected $config = array();

	/**
	 * Property debugExtension.
	 *
	 * @var  \Twig_Extension
	 */
	protected $debugExtension = null;

	/**
	 * render
	 *
	 * @param string        $file
	 * @param array|object  $data
	 *
	 * @throws  \UnexpectedValueException
	 * @return  string
	 */
	public function render($file, $data = array())
	{
		$file = pathinfo($file, PATHINFO_EXTENSION) == 'twig' ? $file : $file . '.twig';

		$this->extensions = array_merge($this->extensions, (array) $this->config->get('extensions', array()));

		return $this->getEngine()->render($file, $data);
	}

	/**
	 * getLoader
	 *
	 * @return  \Twig_Loader_Filesystem
	 */
	public function getLoader()
	{
		if (!$this->loader)
		{
			$this->loader = new \Twig_Loader_Filesystem(iterator_to_array(clone $this->getPaths()));
		}

		return $this->loader;
	}

	/**
	 * setLoader
	 *
	 * @param   \Twig_Loader_Filesystem $loader
	 *
	 * @return  TwigRenderer  Return self to support chaining.
	 */
	public function setLoader(\Twig_Loader_Filesystem $loader)
	{
		$this->loader = $loader;

		return $this;
	}

	/**
	 * addExtension
	 *
	 * @param \Twig_Extension $extension
	 *
	 * @return  static
	 */
	public function addExtension(\Twig_Extension $extension)
	{
		$this->extensions[] = $extension;

		return $this;
	}

	/**
	 * getTwig
	 *
	 * @param bool $new
	 *
	 * @return  \Twig_environment
	 */
	public function getEngine($new = false)
	{
		if (!($this->engine instanceof \Twig_Environment) || $new)
		{
			$this->engine = new \Twig_Environment($this->getLoader(), $this->config->toArray());

			foreach (GlobalContainer::getExtensions() as $extension)
			{
				$this->engine->addExtension($extension);
			}

			foreach ($this->extensions as $extension)
			{
				$this->engine->addExtension($extension);
			}

			foreach (GlobalContainer::getGlobals() as $name => $value)
			{
				$this->engine->addGlobal($name, $value);
			}

			if ($this->config->get('debug'))
			{
				$this->engine->addExtension($this->getDebugExtension());
			}
		}

		return $this->engine;
	}

	/**
	 * setTwig
	 *
	 * @param   \Twig_environment $twig
	 *
	 * @return  TwigRenderer  Return self to support chaining.
	 */
	public function setEngine($twig)
	{
		if (!($twig instanceof \Twig_Environment))
		{
			throw new \InvalidArgumentException('Engine object should be Twig_environment');
		}

		$this->engine = $twig;

		return $this;
	}

	/**
	 * Method to get property DebugExtension
	 *
	 * @return  \Twig_Extension
	 */
	public function getDebugExtension()
	{
		if (!$this->debugExtension)
		{
			$this->debugExtension = new \Twig_Extension_Debug;
		}

		return $this->debugExtension;
	}

	/**
	 * Method to set property debugExtension
	 *
	 * @param   \Twig_Extension $debugExtension
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDebugExtension(\Twig_Extension $debugExtension)
	{
		$this->debugExtension = $debugExtension;

		return $this;
	}

	/**
	 * Method to get property Extensions
	 *
	 * @return  array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Method to set property extensions
	 *
	 * @param   array $extensions
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setExtensions($extensions)
	{
		$this->extensions = $extensions;

		return $this;
	}
}
