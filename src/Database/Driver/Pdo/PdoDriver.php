<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\DatabaseReader;
use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Query\Query\PreparableInterface;
use Windwalker\Query\Query;

/**
 * Class PdoDriver
 *
 * @since 1.0
 */
abstract class PdoDriver extends DatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $name = 'pdo';

	/**
	 * The prepared statement.
	 *
	 * @var    \PDOStatement
	 * @since  1.0
	 */
	protected $cursor;

	/**
	 * The database connection resource.
	 *
	 * @var    \PDO
	 * @since  1.0
	 */
	protected $connection;

	/**
	 * Property reader.
	 *
	 * @var  PdoReader
	 */
	protected $reader = null;

	/**
	 * Constructor.
	 *
	 * @param   \PDO  $connection The pdo connection object.
	 * @param   array $options    List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct(\PDO $connection = null, $options = array())
	{
		$defaultOptions = array(
			'driver'   => 'odbc',
			'dsn'      => '',
			'host'     => 'localhost',
			'database' => '',
			'user'     => '',
			'password' => '',
			'driverOptions' => array()
		);

		$options = array_merge($defaultOptions, $options);

		// Finalize initialisation
		parent::__construct($connection, $options);
	}

	/**
	 * connect
	 *
	 * @throws  \RuntimeException
	 * @return  static
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return $this;
		}

		$dsn = PdoHelper::getDsn($this->options['driver'], $this->options);

		try
		{
			$this->connection = new \PDO(
				$dsn,
				$this->options['user'],
				$this->options['password'],
				$this->options['driverOptions']
			);
		}
		catch (\PDOException $e)
		{
			throw new \RuntimeException('Could not connect to PDO: ' . $e->getMessage() . '. DSN: ' . $dsn, $e->getCode(), $e);
		}

		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

		return $this;
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function disconnect()
	{
		$this->freeResult();

		unset($this->connection);
	}

	/**
	 * Retrieve a PDO database connection attribute
	 * http://www.php.net/manual/en/pdo.getattribute.php
	 *
	 * Usage: $db->getOption(PDO::ATTR_CASE);
	 *
	 * @param   mixed  $key  One of the PDO::ATTR_* Constants
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		$this->connect();

		return $this->connection->getAttribute($key);
	}

	/**
	 * Sets an attribute on the PDO database handle.
	 * http://www.php.net/manual/en/pdo.setattribute.php
	 *
	 * Usage: $db->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);
	 *
	 * @param   integer  $key    One of the PDO::ATTR_* Constants
	 * @param   mixed    $value  One of the associated PDO Constants
	 *                           related to the particular attribute
	 *                           key.
	 *
	 * @return boolean
	 *
	 * @since  1.0
	 */
	public function setOption($key, $value)
	{
		$this->connect();

		return $this->connection->setAttribute($key, $value);
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   1.0
	 */
	public function getVersion()
	{
		$this->connect();

		return $this->getOption(\PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string $database The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		return true;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query          The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   array    $driverOptions  The optional PDO driver options
	 *
	 * @return  PdoDriver  This object to support method chaining.
	 *
	 * @since   1.0
	 */
	public function setQuery($query, $driverOptions = array())
	{
		$this->connect()->freeResult();

		$query = $this->replacePrefix((string) $query);

		// Set query string into PDO, but keep query object in $this->query that we can bind params when execute().
		$this->cursor = $this->connection->prepare($query, $driverOptions);

		// Store reference to the DatabaseQuery instance:
		parent::setQuery($query);

		return $this;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function setUTF()
	{
		return false;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @throws \RuntimeException
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		if (!($this->cursor instanceof \PDOStatement))
		{
			throw new \RuntimeException('PDOStatement not prepared. Maybe you haven\'t set any query');
		}

		// Bind the variables:
		if ($this->query instanceof PreparableInterface)
		{
			$bounded =& $this->query->getBounded();

			foreach ($bounded as $key => $data)
			{
				$this->cursor->bindParam($key, $data->value, $data->dataType, $data->length, $data->driverOptions);
			}
		}

		try
		{
			$this->cursor->execute();
		}
		catch (\PDOException $e)
		{
			throw new \RuntimeException('SQL: ' . implode(", ", $this->cursor->errorInfo()), $this->cursor->errorCode(), $e);
		}

		return $this;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  static
	 *
	 * @since   1.0
	 */
	public function freeResult($cursor = null)
	{
		$cursor = $cursor ? : $this->cursor;

		if ($cursor instanceof \PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		return $this;
	}

	/**
	 * getReader
	 *
	 * @param Query $query
	 *
	 * @return  DatabaseReader
	 */
	public function getReader($query = null)
	{
		if ($query)
		{
			$this->setQuery($query)->execute();
		}

		if (!$this->reader)
		{
			$this->reader = new PdoReader($this);
		}

		return $this->reader;
	}
}
 