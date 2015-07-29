<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\AbstractDatabase;
use Windwalker\Database\Command\AbstractReader;
use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Command\AbstractTransaction;
use Windwalker\Database\Command\AbstractWriter;
use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Query\Query\PreparableInterface;
use Windwalker\Query\Query;

/**
 * Class PdoDriver
 *
 * @since 2.0
 */
class PdoDriver extends DatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $name = 'pdo';

	/**
	 * The prepared statement.
	 *
	 * @var    \PDOStatement
	 * @since  2.0
	 */
	protected $cursor;

	/**
	 * The database connection resource.
	 *
	 * @var    \PDO
	 * @since  2.0
	 */
	protected $connection;

	/**
	 * Property driverOptions.
	 *
	 * @var mixed
	 */
	protected $driverOptions;

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
	 * @since   2.0
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

		$this->name = $options['driver'];

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
	 * @since   2.0
	 */
	public function disconnect()
	{
		$this->freeResult();

		$this->connection = null;
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
	 * @since   2.0
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
	 * @since  2.0
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
	 * @since   2.0
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
	 * @return  static
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->database = $database;

		$this->getDatabase($database)->select();

		return $this;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query          The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   array    $driverOptions  The optional PDO driver options
	 *
	 * @return  PdoDriver  This object to support method chaining.
	 *
	 * @since   2.0
	 */
	public function setQuery($query, $driverOptions = array())
	{
		$this->connect()->freeResult();

		$this->driverOptions = $driverOptions;

		// Store reference to the DatabaseQuery instance:
		parent::setQuery($query);

		return $this;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @throws \RuntimeException
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   2.0
	 */
	public function doExecute()
	{
		// Replace prefix
		$query = $this->replacePrefix((string) $this->query);

		// Set query string into PDO, but keep query object in $this->query that we can bind params when execute().
		$this->cursor = $this->connection->prepare($query, $this->driverOptions);

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
			throw new \RuntimeException($e->getMessage() . "\nSQL: " . $this->query, (int) $e->getCode(), $e);
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
	 * @since   2.0
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
	 * Get the current query object or a new Query object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new Query object.
	 *
	 * @return  Query  The current query object or a new object extending the Query class.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Derive the class name from the driver.
			$class = '\\Windwalker\\Query\\' . ucfirst($this->options['driver']) . '\\' . ucfirst($this->options['driver']) . 'Query';

			// Make sure we have a query class for this driver.
			if (class_exists($class))
			{
				$this->connect();

				return new $class($this->getConnection());
			}

			return parent::getQuery($new);
		}
		else
		{
			return $this->query;
		}
	}

	/**
	 * getDatabaseList
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	public function listDatabases()
	{
		$builder = sprintf('Windwalker\\Query\\%s\\%sQueryBuilder', $this->options['driver'], $this->options['driver']);

		if (!class_exists($builder))
		{
			throw new \LogicException($builder . ' not found, you should implement ' . __METHOD__ . ' in current deriver class.');
		}

		/** @var $builder \Windwalker\Query\QueryBuilderInterface */

		return $this->setQuery($builder::showDatabases())->loadColumn();
	}
}
