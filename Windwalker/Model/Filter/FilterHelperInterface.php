<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model\Filter;

/**
 * Class QueryHelperInterface
 *
 * @since 1.0
 */
interface FilterHelperInterface
{
	/**
	 * setHandler
	 *
	 * @param string   $name
	 * @param callback $handler
	 *
	 * @return  FilterHelperInterface
	 */
	public function setHandler($name, $handler);

	/**
	 * execute
	 *
	 * @param \JDatabaseQuery $query
	 * @param array           $data
	 *
	 * @return  \JDatabaseQuery
	 */
	public function execute(\JDatabaseQuery $query, $data = array());
}
