<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Driver\Pdo\PdoDriver;

/**
 * Class MysqlDriver
 *
 * @since 1.0
 */
class MysqlDriver extends PdoDriver
{
	protected $name = 'mysql';

	/**
	 * getTable
	 *
	 * @param string $name
	 *
	 * @return  MysqlTable
	 */
	public function getTable($name)
	{
		if (empty($this->tables[$name]))
		{
			$this->tables[$name] = new MysqlTable($name, $this);
		}

		return $this->tables[$name];
	}
}
 