<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Cache\Storage;

/**
 * The PhpFileStorage class.
 *
 * @since  {DEPLOY_VERSION}
 */
class PhpFileStorage extends FileStorage
{
	/**
	 * read
	 *
	 * @param   string $filename
	 *
	 * @return  string
	 */
	protected function read($filename)
	{
		return include $filename;
	}

	/**
	 * write
	 *
	 * @param string $filename
	 * @param string $value
	 * @param int    $options
	 *
	 * @return  boolean
	 */
	protected function write($filename, $value, $options)
	{
		return parent::write($filename, $value, $options);
	}
}
