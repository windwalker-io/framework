<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Language\Loader;

/**
 * Class AbstractLoader
 *
 * @since 1.0
 */
abstract class AbstractLoader implements LoaderInterface
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = '';

	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}
}
 