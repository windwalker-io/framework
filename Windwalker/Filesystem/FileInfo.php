<?php
/**
 * Part of the Windwalker project Filesystem Package
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @deprecated  File deprecated, this is just a test class.
 */

namespace Windwalker\Filesystem;

/**
 * A SplFileInfo wrapper to handle dot files when running recursive.
 *
 * @since       1.0
 * @deprecated  File deprecated, this is just a test class.
 */
class FileInfo
{
	/**
	 * SplFileInfo object.
	 *
	 * @var \SplFileInfo
	 */
	protected $file;

	/**
	 * Fileinfo constructor.
	 *
	 * @param  \SplFileInfo $file The file object have to been wrapped.
	 *
	 * @since  1.0
	 */
	public function __construct(\SplFileInfo $file)
	{
		$this->file = $file;
	}

	/**
	 * We remove . and .. when fetching folders' path.
	 *
	 * @return  string  Folder full path without dot.
	 *
	 * @since  1.0
	 */
	public function getPathname()
	{
		$name = $this->file->getPathname();

		$endletters = DIRECTORY_SEPARATOR . '.';

		if (substr($name, -2) == $endletters)
		{
			$name = substr($name, 0, -2);
		}

		return $name;
	}

	/**
	 * Magic function caller to get fileinfo methods.
	 *
	 * @param  string $name Called method name.
	 * @param  array  $args Called method arguments.
	 *
	 * @return  mixed  Return value from fileinfo method.
	 *
	 * @since  1.0
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->file, $name), $args);
	}

	/**
	 * Convert fileinfo to string.
	 *
	 * @return  string  __toStringReturn
	 *
	 * @since  1.0
	 */
	public function __toString()
	{
		return $this->file->getPathname();
	}
}
