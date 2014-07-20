<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Renderer;

use Windwalker\Registry\Registry;

/**
 * Class AbstractRenderer
 *
 * @since 1.0
 */
abstract class AbstractRenderer implements RendererInterface
{
	/**
	 * Property paths.
	 *
	 * @var  \SplPriorityQueue
	 */
	protected $paths = null;

	/**
	 * Property config.
	 *
	 * @var  Registry
	 */
	protected $config = array();

	/**
	 * Class init.
	 *
	 * @param \SplPriorityQueue $paths
	 * @param array             $config
	 */
	public function __construct($paths = null, $config = array())
	{
		$this->setPaths($paths);

		$this->config = new Registry($this->config);

		$this->config->loadArray($config);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     ViewInterface::escape()
	 * @since   1.0
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * finFile
	 *
	 * @param string $file
	 * @param string $ext
	 *
	 * @return  string
	 */
	public function findFile($file, $ext = '')
	{
		$paths = clone $this->paths;

		$file = str_replace('.', '/', $file);

		$ext = $ext ? '.' . trim($ext, '.') : '';

		foreach ($paths as $path)
		{
			$filePath = $path . '/' . $file . $ext;

			if (is_file($filePath))
			{
				return realpath($filePath);
			}
		}

		return null;
	}

	/**
	 * getPaths
	 *
	 * @return  \SplPriorityQueue
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * setPaths
	 *
	 * @param   \SplPriorityQueue $paths
	 *
	 * @return  AbstractRenderer  Return self to support chaining.
	 */
	public function setPaths($paths)
	{
		if (!($paths instanceof \SplPriorityQueue))
		{
			$priority = new \SplPriorityQueue;

			foreach ((array) $paths as $path)
			{
				$priority->insert($path, 1);
			}

			$paths = $priority;
		}

		$this->paths = $paths;

		return $this;
	}

	/**
	 * addPath
	 *
	 * @param string  $path
	 * @param integer $priority
	 *
	 * @return  void
	 */
	public function addPath($path, $priority = 0)
	{
		$this->paths->insert($path, $priority);
	}
}
 