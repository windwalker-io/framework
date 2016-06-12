<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Loader;

/**
 * The EdgeFileLoader class.
 *
 * @since  {DEPLOY_VERSION}
 */
class EdgeFileLoader
{
	protected $extensions = array('.edge.php', '.blade.php');

	protected $paths = array();

	public function loadFile($file)
	{
		$file = $this->normalize($file);

		$filePath = null;

		foreach ($this->paths as $path)
		{
			foreach ($this->extensions as $ext)
			{
				if (is_file($path . '/' . $file . $ext))
				{
					$filePath = $path . '/' . $file . $ext;

					break 2;
				}
			}
		}

		if ($filePath === null)
		{
			throw new \UnexpectedValueException('File not found: ' . $file);
		}

		return $filePath;
	}

	public function addPath($path)
	{
		$this->paths[] = $path;
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
}
