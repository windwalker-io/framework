<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Loader;

use Windwalker\Loader\Loader\FileMappingLoader;
use Windwalker\Loader\Loader\Psr0Loader;
use Windwalker\Loader\Loader\Psr4Loader;

/**
 * Class ClassLoader
 *
 * @since 1.0
 */
class ClassLoader
{
	/**
	 * Property psr0.
	 *
	 * @var  Psr0Loader
	 */
	protected $psr0 = null;

	/**
	 * Property psr4.
	 *
	 * @var  Psr4Loader
	 */
	protected $psr4 = null;

	/**
	 * Property files.
	 *
	 * @var  FileMappingLoader
	 */
	protected $files = null;

	/**
	 * Class init.
	 *
	 * @param FileMappingLoader $files
	 * @param Psr0Loader        $psr0
	 * @param Psr4Loader        $psr4
	 */
	public function __construct(FileMappingLoader $files = null, Psr0Loader $psr0 = null, Psr4Loader $psr4 = null)
	{
		$this->files = $files ? : new FileMappingLoader;
		$this->psr0  = $psr0 ? : new Psr0Loader;
		$this->psr4  = $psr4 ? : new Psr4Loader;
	}

	/**
	 * register
	 *
	 * @return  ClassLoader
	 */
	public function register()
	{
		$this->files->register();
		$this->psr0->register();
		$this->psr4->register();

		return $this;
	}

	/**
	 * unregister
	 *
	 * @return  $this
	 */
	public function unregister()
	{
		$this->files->unregister();
		$this->psr0->unregister();
		$this->psr4->unregister();

		return $this;
	}

	/**
	 * addPsr0
	 *
	 * @param string|array $class
	 * @param string       $path
	 *
	 * @return  $this
	 */
	public function addPsr0($class, $path = null)
	{
		if (is_array($class))
		{
			foreach ($class as $ns => $path)
			{
				$this->psr0->addNamespace($ns, $path);
			}
		}
		else
		{
			$this->psr0->addNamespace($class, $path);
		}

		return $this;
	}

	/**
	 * addPsr4
	 *
	 * @param string|array $class
	 * @param string       $path
	 *
	 * @return  $this
	 */
	public function addPsr4($class, $path = null)
	{
		if (is_array($class))
		{
			foreach ($class as $ns => $path)
			{
				$this->psr4->addNamespace($ns, $path);
			}
		}
		else
		{
			$this->psr4->addNamespace($class, $path);
		}

		return $this;
	}

	/**
	 * addMap
	 *
	 * @param string|array $class
	 * @param string       $path
	 *
	 * @return  $this
	 */
	public function addMap($class, $path = null)
	{
		if (is_array($class))
		{
			foreach ($class as $ns => $path)
			{
				$this->files->addMap($ns, $path);
			}
		}
		else
		{
			$this->files->addMap($class, $path);
		}

		return $this;
	}
}

