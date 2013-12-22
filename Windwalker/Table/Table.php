<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Table;

/**
 * Class Table
 *
 * @since 1.0
 */
class Table extends \JTable
{
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string           $table  Name of the table to model.
	 * @param   mixed            $key    Name of the primary key field in the table or array of field names that compose the primary key.
	 * @param   \JDatabaseDriver $db     JDatabaseDriver object.
	 */
	public function __construct($table, $key = 'id', $db = null)
	{
		$db = $db ?: \JFactory::getDbo();

		parent::__construct($table, $key, $db);
	}
}
