<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Driver\Pdo\PdoReader;

/**
 * Class PostgresqlReader
 *
 * @since 2.0
 */
class PostgresqlReader extends PdoReader
{
	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @param   string  $name  Name of the sequence object from which the ID should be returned.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   2.1
	 */
	public function insertId()
	{
		$insertQuery = $this->db->getQuery();

		$table = $insertQuery->insert->getElements();

		/* find sequence column name */
		$colNameQuery = $this->db->getQuery(true);

		$colNameQuery->select('column_default')
			->from('information_schema.columns')
			->where("table_name=" . $this->db->quote($this->db->replacePrefix(trim($table[0], '"'))))
			->where("column_default LIKE '%nextval%'");

		$colName = $this->db->getReader($colNameQuery)->loadArray();

		$changedColName = str_replace('nextval', 'currval', $colName);

		$insertidQuery = $this->db->getQuery(true);

		$insertidQuery->select($changedColName);

		$insertVal = $this->db->getReader($insertidQuery)->loadArray();

		return $insertVal[0];
	}
}
