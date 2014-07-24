<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Filter\Type;

/**
 * Class AbstractFilterType
 *
 * @since 1.0
 */
abstract class AbstractFilterType
{
	/**
	 * filter
	 *
	 * @param string $source
	 *
	 * @return  mixed
	 */
	abstract public function filter($source);

	/**
	 * __invoke
	 *
	 * @param string $source
	 *
	 * @return  mixed
	 */
	public function __invoke($source)
	{
		return $this->filter($source);
	}
}
 