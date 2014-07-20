<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Renderer;

/**
 * Class PhpRenderer
 *
 * @since 1.0
 */
class TwigRenderer extends AbstractRenderer
{
	/**
	 * Property twig.
	 *
	 * @var  \Twig_environment
	 */
	protected $twig = null;

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
	 * @var  \Joomla\Registry\Registry|array
	 */
	protected $config = array(
		'debug' => FORMOSA_DEBUG
	);

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
		$this->extensions = array_merge($this->extensions, (array) $this->config->get('extension', array()));

		return $this->getTwig()->render($file, $data);
	}

	/**
	 * getLoader
	 *
	 * @return  null
	 */
	public function getLoader()
	{
		if (!$this->loader)
		{
			$this->loader = new \Twig_Loader_Filesystem(iterator_to_array($this->getPaths()));
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
	 * @return  void
	 */
	public function addExtension(\Twig_Extension $extension)
	{
		$this->extensions[] = $extension;
	}

	/**
	 * getTwig
	 *
	 * @return  \Twig_environment
	 */
	protected function getTwig()
	{
		if (!($this->twig instanceof \Twig_Environment))
		{
			$this->twig = new \Twig_Environment($this->getLoader(), $this->config->toArray());

			foreach ($this->extensions as $extension)
			{
				$this->twig->addExtension($extension);
			}

			if (FORMOSA_DEBUG)
			{
				$this->twig->addExtension(new \Twig_Extension_Debug);
			}
		}

		return $this->twig;
	}

	/**
	 * setTwig
	 *
	 * @param   \Twig_environment $twig
	 *
	 * @return  TwigRenderer  Return self to support chaining.
	 */
	public function setTwig(\Twig_environment $twig)
	{
		$this->twig = $twig;

		return $this;
	}
}
