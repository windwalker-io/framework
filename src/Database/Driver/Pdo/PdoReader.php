<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\AbstractReader;
use Windwalker\Query\Query;

/**
 * Class PdoReader
 *
 * @since 2.0
 */
class PdoReader extends AbstractReader
{
	/**
	 * Property cursor.
	 *
	 * @var  \PDOStatement
	 */
	protected $cursor;

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   2.0
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
	 * @since   2.0
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
	 * @since   2.0
	 */
	public function fetchObject($class = 'stdClass')
	{
		$this->execute();

		if (!$this->cursor)
		{
			return false;
		}

		return $this->cursor->fetchObject($class);
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
		$this->execute();

		if (!$this->cursor)
		{
			return false;
		}

		return $this->cursor->fetch($type);
	}

	/**
	 * fetchAll
	 *
	 * @param int   $type
	 * @param array $args
	 * @param array $ctorArgs
	 *
	 * @see http://php.net/manual/en/pdostatement.fetchall.php
	 *
	 * @return  array|bool
	 */
	public function fetchAll($type = \PDO::FETCH_ASSOC, $args = null, $ctorArgs = null)
	{
		$this->execute();

		if (!$this->cursor)
		{
			return false;
		}

		return $this->cursor->fetchAll($type);
	}

	/**
	 * count
	 *
	 * @return  integer
	 */
	public function count()
	{
		$this->execute();

		if (!$this->cursor)
		{
			return 0;
		}

		return $this->cursor->rowCount();
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 * Only applicable for DELETE, INSERT, or UPDATE statements.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   2.0
	 */
	public function countAffected()
	{
		$this->execute();

		if (!$this->cursor)
		{
			return 0;
		}

		return $this->cursor->rowCount();
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   2.0
	 */
	public function insertId()
	{
		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->db->getConnection()->lastInsertId();
	}
}

