<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\DatabaseReader;
use Windwalker\Query\Query;

/**
 * Class PdoReader
 *
 * @since 1.0
 */
class PdoReader extends DatabaseReader
{
	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchArray()
	{
		return $this->fetch(\PDO::FETCH_NUM);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchAssoc()
	{
		return $this->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   string  $class  Unused, only necessary so method signature will be the same as parent.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchObject($class = '\\stdClass')
	{
		return $this->db->getCursor()->fetchObject($class);
	}

	/**
	 * fetch
	 *
	 * @param int  $type
	 * @param int  $ori
	 * @param int  $offset
	 *
	 * @see http://php.net/manual/en/pdostatement.fetch.php
	 *
	 * @return  bool|mixed
	 */
	public function fetch($type = \PDO::FETCH_ASSOC, $ori = null, $offset = 0)
	{
		return $this->db->getCursor()->fetch($type);
	}

	/**
	 * fetchAll
	 *
	 * @param int  $type
	 * @param null $args
	 * @param null $ctorArgs
	 *
	 * @see http://php.net/manual/en/pdostatement.fetchall.php
	 *
	 * @return  array|bool
	 */
	public function fetchAll($type = \PDO::FETCH_ASSOC, $args = null, $ctorArgs = null)
	{
		return $this->db->getCursor()->fetchAll($type);
	}

	/**
	 * count
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->db->getCursor()->rowCount();
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 * Only applicable for DELETE, INSERT, or UPDATE statements.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   1.0
	 */
	public function countAffected()
	{
		return $this->db->getCursor()->rowCount();
	}
}
 