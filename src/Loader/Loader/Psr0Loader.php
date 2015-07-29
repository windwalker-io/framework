<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Loader\Loader;

/**
 * Class Psr0Loader
 *
 * @note This class based on SplClassLoader class.
 *       See: https://gist.github.com/jwage/221634
 *
 * @since 2.0
 */
class Psr0Loader extends AbstractLoader
{
	/**
	 * Property fileExtension.
	 *
	 * @var  string
	 */
	private $fileExtension = '.php';

	/**
	 * Property namespace.
	 *
	 * @var  string
	 */
	private $namespaces = array();

	/**
	 * Property namespaceSeparator.
	 *
	 * @var  string
	 */
	private $namespaceSeparator = '\\';

	/**
	 * Creates a new instance that loads classes of the
	 * specified namespace.
	 *
	 * @param array $namespaces The namespaces and path mapping.
	 */
	public function __construct(array $namespaces = array())
	{
		$this->namespaces = $namespaces;
	}

	/**
	 * addNamespace
	 *
	 * @param string $prefix
	 * @param string $path
	 *
	 * @return  Psr0Loader
	 */
	public function addNamespace($prefix, $path)
	{
		// Normalize namespace prefix
		$prefix = trim($prefix, '\\');

		// Normalize the base directory with a trailing separator
		$path = rtrim($path, '/') . DIRECTORY_SEPARATOR;
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		$this->namespaces[$prefix] = $path;

		return $this;
	}

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 *
	 * @return Psr0Loader
	 */
	public function setNamespaceSeparator($sep)
	{
		$this->namespaceSeparator = $sep;

		return $this;
	}

	/**
	 * Gets the namespace seperator used by classes in the namespace of this class loader.
	 *
	 * @return string
	 */
	public function getNamespaceSeparator()
	{
		return $this->namespaceSeparator;
	}

	/**
	 * Sets the file extension of class files in the namespace of this class loader.
	 *
	 * @param string $fileExtension
	 *
	 * @return Psr0Loader
	 */
	public function setFileExtension($fileExtension)
	{
		$this->fileExtension = $fileExtension;

		return $this;
	}

	/**
	 * Gets the file extension of class files in the namespace of this class loader.
	 *
	 * @return string $fileExtension
	 */
	public function getFileExtension()
	{
		return $this->fileExtension;
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 *
	 * @return Psr0Loader
	 */
	public function loadClass($className)
	{
		foreach ($this->namespaces as $namespace => $path)
		{
			if (null === $namespace || $namespace === substr($className, 0, strlen($namespace)))
			{
				$fileName  = '';
				$namespace = '';

				if (false !== ($lastNsPos = strripos($className, $this->namespaceSeparator)))
				{
					$namespace = substr($className, 0, $lastNsPos);
					$className = substr($className, $lastNsPos + 1);
					$fileName  = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
				}

				$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->fileExtension;

				$this->requireFile(($path !== null ? $path . DIRECTORY_SEPARATOR : '') . $fileName);

				break;
			}
		}

		return $this;
	}
}
