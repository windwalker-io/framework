<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Renderer\Twig;

/**
 * The TwigFilesystemLoader class.
 *
 * @since  {DEPLOY_VERSION}
 */
class TwigFilesystemLoader extends \Twig_Loader_Filesystem
{
	/**
	 * Property separator.
	 *
	 * @var  string
	 */
	protected $separator;

	/**
	 * TwigFilesystemLoader constructor.
	 *
	 * @param array|string $paths
	 * @param string       $separator
	 */
	public function __construct($paths, $separator = '.')
	{
		$this->separator = $separator;

		parent::__construct($paths);
	}

	/**
	 * Method to get property Separator
	 *
	 * @return  string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * Method to set property separator
	 *
	 * @param   string $separator
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSeparator($separator)
	{
		$this->separator = $separator;

		return $this;
	}

	/**
	 * normalizeName
	 *
	 * @param   string  $name
	 *
	 * @return  string
	 */
	protected function normalizeName($name)
	{
		$ext = pathinfo($name, PATHINFO_EXTENSION);

		if ($ext == 'twig')
		{
			$name = substr($name, 0, -5);
		}

		$path = preg_replace('#/{2,}#', '/', str_replace('.', '/', $name));

		return $path . '.twig';
	}
}
