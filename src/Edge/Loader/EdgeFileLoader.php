<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Loader;

/**
 * The EdgeFileLoader class.
 *
 * @since  3.0
 */
class EdgeFileLoader implements EdgeLoaderInterface
{
	/**
	 * Property extensions.
	 *
	 * @var  array
	 */
	protected $extensions = array('.edge.php', '.blade.php');

	/**
	 * Property paths.
	 *
	 * @var  array
	 */
	protected $paths = array();

	/**
	 * EdgeFileLoader constructor.
	 *
	 * @param array $paths
	 */
	public function __construct(array $paths = array())
	{
		$this->paths = $paths;
	}

	/**
	 * find
	 *
	 * @param string $key
	 *
	 * @return  string
	 */
	public function find($key)
	{
		$key = $this->normalize($key);

		$filePath = null;

		foreach ($this->paths as $path)
		{
			foreach ($this->extensions as $ext)
			{
				if (is_file($path . '/' . $key . $ext))
				{
					$filePath = $path . '/' . $key . $ext;

					break 2;
				}
			}
		}

		if ($filePath === null)
		{
			$paths = implode(" |\n ", $this->paths);

			throw new \UnexpectedValueException('View file not found: ' . $key . ".\n (Paths: " . $paths . ')');
		}

		return $filePath;
	}

	/**
	 * loadFile
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function load($path)
	{
		return file_get_contents($path);
	}

	/**
	 * addPath
	 *
	 * @param   string  $path
	 *
	 * @return  static
	 */
	public function addPath($path)
	{
		$this->paths[] = $path;

		return $this;
	}

	/**
	 * prependPath
	 *
	 * @param   string  $path
	 *
	 * @return  static
	 */
	public function prependPath($path)
	{
		array_unshift($this->paths, $path);

		return $this;
	}

	/**
	 * normalize
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	protected function normalize($path)
	{
		return str_replace('.', '/', $path);
	}

	/**
	 * Method to get property Paths
	 *
	 * @return  array
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method to set property paths
	 *
	 * @param   array $paths
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * addExtension
	 *
	 * @param   string  $name
	 *
	 * @return  static
	 */
	public function addFileExtension($name)
	{
		$this->extensions[] = $name;
		
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
