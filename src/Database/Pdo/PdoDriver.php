<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Pdo;

use Psr\Log\LogLevel;
use Windwalker\Database\DatabaseDriver;
use Windwalker\Query\Query\PreparableInterface;

/**
 * Class PdoDriver
 *
 * @since 1.0
 */
class PdoDriver extends DatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'pdo';

	/**
	 * The prepared statement.
	 *
	 * @var    \PdoStatement
	 * @since  1.0
	 */
	protected $prepared;

	/**
	 * Contains the current query execution status
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $executed = false;

	/**
	 * The database connection resource.
	 *
	 * @var    \PDO
	 * @since  1.0
	 */
	protected $connection;

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
			throw new \RuntimeException('Could not connect to PDO: ' . $e->getMessage() . '. DSN: ' . $dsn, 2, $e);
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
		$this->connect();

		$this->freeResult();

		$sql = $this->replacePrefix((string) $query);

		$this->prepared = $this->connection->prepare($sql, $driverOptions);

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
	 * @throws \PDOException
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->connect();

		if (!is_object($this->connection))
		{
			throw new \RuntimeException('Database disconnected.');
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log(LogLevel::DEBUG, '{sql}', array('sql' => $sql, 'category' => 'databasequery', 'trace' => debug_backtrace()));
		}

		// Execute the query.
		$this->executed = false;

		if (!($this->prepared instanceof \PDOStatement))
		{
			throw new \RuntimeException('PDOStatement not prepared. Maybe you haven\'t set any query');
		}

		// Bind the variables:
		if ($this->sql instanceof PreparableInterface)
		{
			$bounded =& $this->sql->getBounded();

			foreach ($bounded as $key => $obj)
			{
				$this->prepared->bindParam($key, $obj->value, $obj->dataType, $obj->length, $obj->driverOptions);
			}
		}

		try
		{
			$this->prepared->execute();
		}
		catch (\PDOException $e)
		{
			// Get the error number and message before we execute any more queries.
			$errorNum = (int) $this->prepared->errorCode();
			$errorMsg = (string) 'SQL: ' . implode(", ", $this->prepared->errorInfo());

			// Throw the normal query exception.
			$this->log(LogLevel::ERROR, 'Database query failed (error #{code}): {message}', array('code' => $errorNum, 'message' => $errorMsg));

			throw $e;
		}

		return $this->prepared;
	}



	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function freeResult($cursor = null)
	{
		$this->executed = false;

		if ($cursor instanceof \PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->prepared instanceof \PDOStatement)
		{
			$this->prepared->closeCursor();
			$this->prepared = null;
		}
	}
}
 