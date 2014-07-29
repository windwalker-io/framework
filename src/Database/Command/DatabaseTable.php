<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Database\Driver\DatabseAwareTrait;

/**
 * Class DatabaseTable
 *
 * @since 1.0
 */
abstract class DatabaseTable
{
	use DatabseAwareTrait
	{
		DatabseAwareTrait::__construct as doConstruct;
	}

	protected $table = null;

	/**
	 * Constructor.
	 *
	 * @param string         $table
	 * @param DatabaseDriver $db
	 */
	public function __construct($table, DatabaseDriver $db)
	{
		$this->table = $table;

		$this->doConstruct($db);
	}

	/**
	 * Get table columns.
	 *
	 * @return  array Table columns with type.
	 */
	abstract public function getColumns();
}
 