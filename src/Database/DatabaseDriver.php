<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Windwalker\Query\Query;

/**
 * Class DatabaseDriver
 *
 * @since 1.0
 */
abstract class DatabaseDriver implements LoggerAwareInterface
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $name;

	/**
	 * The name of the database.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $database;

	/**
	 * The database connection resource.
	 *
	 * @var    resource
	 * @since  1.0
	 */
	protected $connection;

	/**
	 * The number of SQL statements executed by the database driver.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $count = 0;

	/**
	 * The database connection cursor from the last query.
	 *
	 * @var    resource
	 * @since  1.0
	 */
	protected $cursor;

	/**
	 * The database driver debugging state.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $debug = false;

	/**
	 * Passed in upon instantiation and saved.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $options;

	/**
	 * The current SQL statement to execute.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	protected $sql;

	/**
	 * The common database table prefix.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $tablePrefix;

	/**
	 * True if the database engine supports UTF-8 character encoding.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $utf = true;

	/**
	 * The database error message.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $errorMsg;

	/**
	 * DatabaseDriver instances container.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected static $instances = array();

	/**
	 * The minimum supported database version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $dbMinimum;

	/**
	 * The depth of the current transaction.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $transactionDepth = 0;

	/**
	 * A logger.
	 *
	 * @var    LoggerInterface
	 * @since  1.0
	 */
	protected $logger;

	/**
	 * Constructor.
	 *
	 * @param   null  $connection The database connection instance.
	 * @param   array $options    List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct($connection = null, $options = array())
	{
		// Initialise object variables.
		$this->connection = $connection;

		$this->database = (isset($options['database'])) ? $options['database'] : '';
		$this->tablePrefix = (isset($options['prefix'])) ? $options['prefix'] : 'wind_';

		// Set class options.
		$this->options = $options;
	}

	/**
	 * getConnection
	 *
	 * @return  resource
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * setConnection
	 *
	 * @param   resource $connection
	 *
	 * @return  DatabaseDriver  Return self to support chaining.
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;

		return $this;
	}

	/**
	 * connect
	 *
	 * @return  static
	 */
	abstract public function connect();

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract public function disconnect();

	/**
	 * Gets the name of the database used by this conneciton.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @return  string  The format string.
	 *
	 * @since   1.0
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @return  string  The common database table prefix.
	 *
	 * @since   1.0
	 */
	public function getPrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Get the current query object or a new Query object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new Query object.
	 *
	 * @return  Query  The current query object or a new object extending the Query class.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Derive the class name from the driver.
			$class = '\\Windwalker\\Query\\' . ucfirst($this->name) . '\\' . ucfirst($this->name) . 'Query';

			// Make sure we have a query class for this driver.
			if (!class_exists($class))
			{
				// If it doesn't exist we are at an impasse so throw an exception.
				throw new \RuntimeException('Database Query Class not found.');
			}

			return new $class($this->getConnection());
		}
		else
		{
			return $this->sql;
		}
	}

	/**
	 * Determine whether or not the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 character encoding.
	 *
	 * @since   1.0
	 */
	public function hasUTFSupport()
	{
		return $this->utf;
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   1.0
	 */
	abstract public function getVersion();

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract public function freeResult($cursor = null);

	/**
	 * Logs a message.
	 *
	 * @param   string  $level    The level for the log. Use constants belonging to Psr\Log\LogLevel.
	 * @param   string  $message  The message.
	 * @param   array   $context  Additional context.
	 *
	 * @return  DatabaseDriver  Returns itself to allow chaining.
	 *
	 * @since   1.0
	 */
	public function log($level, $message, array $context = array())
	{
		if ($this->logger)
		{
			$this->logger->log($level, $message, $context);
		}

		return $this;
	}

	/**
	 * Sets a logger instance on the object
	 *
	 * @param   LoggerInterface  $logger  A PSR-3 compliant logger.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   1.0
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		$startPos = 0;
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);

			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);

			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// Quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;

				if ($k === false)
				{
					break;
				}

				$l = $k - 1;

				while ($l >= 0 && $sql{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}

				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}

				break;
			}

			if ($k === false)
			{
				// Error in the query - no end quote; ignore it
				break;
			}

			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}

		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	abstract public function select($database);

	/**
	 * Sets the database debugging state for the driver.
	 *
	 * @param   boolean  $level  True to enable debugging.
	 *
	 * @return  static
	 *
	 * @since   1.0
	 */
	public function setDebug($level)
	{
		$this->debug = (bool) $level;

		return $this;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed  $query The SQL statement to set either as a Query object or a string.
	 *
	 * @return  DatabaseDriver  This object to support method chaining.
	 *
	 * @since   1.0
	 */
	public function setQuery($query)
	{
		$this->sql = $query;

		return $this;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	abstract public function setUTF();

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	abstract public function execute();
}
 