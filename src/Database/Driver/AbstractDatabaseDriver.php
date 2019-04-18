<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver;

use Windwalker\Database\Command\AbstractDatabase;
use Windwalker\Database\Command\AbstractReader;
use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Command\AbstractTransaction;
use Windwalker\Database\Command\AbstractWriter;
use Windwalker\Database\Iterator\DataIterator;
use Windwalker\Database\Monitor\NullMonitor;
use Windwalker\Database\Monitor\QueryMonitorInterface;
use Windwalker\Query\Query;

/**
 * Class DatabaseDriver
 *
 * @since 2.0
 */
abstract class AbstractDatabaseDriver implements DatabaseDriverInterface
{
    /**
     * The name of the database driver.
     *
     * @var    string
     * @since  2.0
     */
    protected $name;

    /**
     * The name of the database.
     *
     * @var    string
     * @since  2.0
     */
    protected $database;

    /**
     * The database connection resource.
     *
     * @var    resource|object
     * @since  2.0
     */
    protected $connection;

    /**
     * The number of SQL statements executed by the database driver.
     *
     * @var    integer
     * @since  2.0
     */
    protected $count = 0;

    /**
     * The database connection cursor from the last query.
     *
     * @var    resource|object
     * @since  2.0
     */
    protected $cursor;

    /**
     * The database driver debugging state.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $debug = false;

    /**
     * Passed in upon instantiation and saved.
     *
     * @var    array
     * @since  2.0
     */
    protected $options;

    /**
     * The current SQL statement to execute.
     *
     * @var    mixed
     * @since  2.0
     */
    protected $query;

    /**
     * The common database table prefix.
     *
     * @var    string
     * @since  2.0
     */
    protected $tablePrefix;

    /**
     * Property reader.
     *
     * @var  AbstractReader
     */
    protected $reader;

    /**
     * Property writer.
     *
     * @var AbstractWriter
     */
    protected $writer;

    /**
     * Property table.
     *
     * @var AbstractTable[]
     */
    protected $tables = [];

    /**
     * Property databases.
     *
     * @var  AbstractDatabase[]
     */
    protected $databases = [];

    /**
     * Property transaction.
     *
     * @var AbstractTransaction
     */
    protected $transaction;

    /**
     * Property lastQuery.
     *
     * @var  string
     */
    protected $lastQuery;

    /**
     * Property monitor.
     *
     * @var QueryMonitorInterface
     */
    protected $monitor;

    /**
     * Property independentQuery.
     *
     * @var  Query
     */
    protected static $independentQuery;

    /**
     * Constructor.
     *
     * @param   null  $connection The database connection instance.
     * @param   array $options    List of options used to configure the connection
     *
     * @throws \ReflectionException
     * @since   2.0
     */
    public function __construct($connection = null, $options = [])
    {
        // Initialise object variables.
        $this->connection = $connection;

        $this->database    = $options['database'] ?? '';
        $this->tablePrefix = $options['prefix'] ?? 'wind_';

        // Set class options.
        $this->options = $options;

        // Prepare Null monitor
        $this->setMonitor(new NullMonitor());
    }

    /**
     * getConnection
     *
     * @return  resource|object
     */
    public function &getConnection()
    {
        return $this->connection;
    }

    /**
     * setConnection
     *
     * @param   resource $connection
     *
     * @return  AbstractDatabaseDriver  Return self to support chaining.
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Execute the SQL statement.
     *
     * @param   mixed $query The SQL statement to set either as a Query object or a string.
     *
     * @return  resource|false  Return Resource to do more or false if query failure.
     *
     * @since   2.0
     * @throws  \RuntimeException
     */
    public function execute($query = null)
    {
        $prepare = true;

        if ($query !== null) {
            $this->setQuery($query);

            $prepare = false;
        }

        $this->connect();

        if (!$this->connection) {
            throw new \RuntimeException('Database disconnected.');
        }

        // Increment the query counter.
        $this->count++;

        return $this->doExecute($prepare);
    }

    /**
     * Sets the SQL statement string for later execution.
     *
     * @param   mixed $query The SQL statement to set either as a Query object or a string.
     *
     * @return  AbstractDatabaseDriver  This object to support method chaining.
     *
     * @since   2.0
     */
    public function prepare($query)
    {
        return $this->setQuery($query);
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
     * @return  static
     *
     * @since   2.0
     */
    abstract public function disconnect();

    /**
     * Execute the SQL statement.
     *
     * @param bool $prepare
     *
     * @return  resource|false  A database cursor resource on success, boolean false on failure.
     *
     * @throws \RuntimeException
     * @since   2.0
     */
    abstract protected function doExecute(bool $prepare = true);

    /**
     * Select a database for use.
     *
     * @param   string $database The name of the database to select for use.
     *
     * @return  boolean  True if the database was successfully selected.
     *
     * @since   2.0
     * @throws  \RuntimeException
     */
    abstract public function select($database);

    /**
     * Get the version of the database connector
     *
     * @return  string  The database connector version.
     *
     * @since   2.0
     */
    abstract public function getVersion();

    /**
     * Method to free up the memory used for the result set.
     *
     * @param   mixed $cursor The optional result set cursor from which to fetch the row.
     *
     * @return  static
     *
     * @since   2.0
     */
    abstract public function freeResult($cursor = null);

    /**
     * getDatabaseList
     *
     * @return  mixed
     */
    abstract public function listDatabases();

    /**
     * getCursor
     *
     * @return  resource
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Get the current query object or a new Query object.
     *
     * @param   boolean $new False to return the current query object, True to return a new Query object.
     *
     * @return  Query  The current query object or a new object extending the Query class.
     *
     * @since   2.0
     * @throws  \RuntimeException
     */
    public function getQuery($new = false)
    {
        if ($new) {
            // Derive the class name from the driver.
            $class = '\\Windwalker\\Query\\' . ucfirst($this->name) . '\\' . ucfirst($this->name) . 'Query';

            // Make sure we have a query class for this driver.
            if (!class_exists($class)) {
                // If it doesn't exist we are at an impasse so throw an exception.
                throw new \RuntimeException('Database Query Class not found.');
            }

            return new $class($this);
        } else {
            return $this->query;
        }
    }

    /**
     * getTable
     *
     * @param string $name
     * @param bool   $new
     *
     * @return  AbstractTable
     */
    public function getTable($name, $new = false)
    {
        if (empty($this->tables[$name]) || $new) {
            $class = sprintf('Windwalker\\Database\\Driver\\%s\\%sTable', ucfirst($this->name), ucfirst($this->name));

            $this->tables[$name] = new $class($name, $this);
        }

        return $this->tables[$name];
    }

    /**
     * getTable
     *
     * @param string $name
     * @param bool   $new
     *
     * @return AbstractDatabase
     */
    public function getDatabase($name = null, $new = false)
    {
        $name = $name ?: $this->database;

        if (empty($this->databases[$name]) || $new) {
            $class = sprintf(
                'Windwalker\\Database\\Driver\\%s\\%sDatabase',
                ucfirst($this->name),
                ucfirst($this->name)
            );

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('Class %s not exists.', $class));
            }

            $this->databases[$name] = new $class($name, $this);
        }

        return $this->databases[$name];
    }

    /**
     * getReader
     *
     * @param Query $query
     * @param bool  $new
     *
     * @return AbstractReader
     */
    public function getReader($query = null, $new = true)
    {
        if ($query) {
            $this->setQuery($query);
        }

        if (!$this->reader || $new) {
            $class = sprintf('Windwalker\\Database\\Driver\\%s\\%sReader', ucfirst($this->name), ucfirst($this->name));

            $this->reader = new $class($this);
        }

        return $this->reader;
    }

    /**
     * getWriter
     *
     * @param bool $new
     *
     * @return AbstractWriter
     */
    public function getWriter($new = true)
    {
        if (!$this->writer || $new) {
            $class = sprintf('Windwalker\\Database\\Driver\\%s\\%sWriter', ucfirst($this->name), ucfirst($this->name));

            $this->writer = new $class($this);
        }

        return $this->writer;
    }

    /**
     * getWriter
     *
     * @param boolean $nested
     * @param bool    $new
     *
     * @return AbstractTransaction
     */
    public function getTransaction($nested = true, $new = false)
    {
        if (!$this->transaction || $new) {
            $class = sprintf(
                'Windwalker\\Database\\Driver\\%s\\%sTransaction',
                ucfirst($this->name),
                ucfirst($this->name)
            );

            $this->transaction = new $class($this, $nested);
        }

        return $this->transaction;
    }

    /**
     * transaction
     *
     * @param callable $callback
     * @param bool     $nested
     * @param bool     $autoCommit
     *
     * @return  AbstractDatabaseDriver
     *
     * @throws \Throwable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function transaction(callable $callback, bool $nested = true, bool $autoCommit = true): self
    {
        $trans = $this->getTransaction($nested);

        $trans->transaction($callback, $autoCommit);

        return $this;
    }

    /**
     * getIterator
     *
     * @param string $class
     *
     * @return  DataIterator
     */
    public function getIterator($class = 'stdClass')
    {
        return $this->getReader()->getIterator($class);
    }

    /**
     * Gets the name of the database used by this conneciton.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCurrentDatabase()
    {
        return $this->database;
    }

    /**
     * Get the common table prefix for the database driver.
     *
     * @return  string  The common database table prefix.
     *
     * @since   2.0
     */
    public function getPrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * This function replaces a string identifier <var>$prefix</var> with the string held is the
     * <var>tablePrefix</var> class variable.
     *
     * @see     https://stackoverflow.com/a/31745275
     *
     * @param   string $sql    The SQL statement to prepare.
     * @param   string $prefix The common table prefix.
     *
     * @return  string  The processed SQL statement.
     *
     * @since   2.0
     */
    public function replacePrefix($sql, $prefix = '#__')
    {
        $array = [];

        if ($number = preg_match_all('#((?<![\\\])[\'"])((?:.(?!(?<![\\\])\1))*.?)\1#i', $sql, $matches)) {
            for ($i = 0; $i < $number; $i++) {
                if (!empty($matches[0][$i])) {
                    $array[$i] = trim($matches[0][$i]);
                    $sql       = str_replace($matches[0][$i], '<#encode:' . $i . ':code#>', $sql);
                }
            }
        }

        $sql = str_replace($prefix, $this->tablePrefix, $sql);

        foreach ($array as $key => $js) {
            $sql = str_replace('<#encode:' . $key . ':code#>', $js, $sql);
        }

        return $sql;

        return $literal;
    }

    /**
     * Splits a string of multiple queries into an array of individual queries.
     *
     * @param   string $sql Input SQL string with which to split into individual queries.
     *
     * @return  array  The queries from the input string separated into an array.
     *
     * @since   2.0
     */
    public static function splitSql($sql)
    {
        $start   = 0;
        $open    = false;
        $char    = '';
        $end     = strlen($sql);
        $queries = [];

        for ($i = 0; $i < $end; $i++) {
            $current = substr($sql, $i, 1);

            if (($current === '"' || $current === '\'')) {
                $n = 2;

                while (substr($sql, $i - $n + 1, 1) === '\\' && $n < $i) {
                    $n++;
                }

                if ($n % 2 == 0) {
                    if ($open) {
                        if ($current == $char) {
                            $open = false;
                            $char = '';
                        }
                    } else {
                        $open = true;
                        $char = $current;
                    }
                }
            }

            if (($current === ';' && !$open) || $i == $end - 1) {
                $queries[] = substr($sql, $start, ($i - $start + 1));
                $start     = $i + 1;
            }
        }

        return $queries;
    }

    /**
     * Sets the database debugging state for the driver.
     *
     * @param   boolean $level True to enable debugging.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function setDebug($level)
    {
        $this->debug = (bool) $level;

        return $this;
    }

    /**
     * Sets the SQL statement string for later execution.
     *
     * @param   mixed $query The SQL statement to set either as a Query object or a string.
     *
     * @return  AbstractDatabaseDriver  This object to support method chaining.
     *
     * @since   2.0
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * loadAll
     *
     * @param string $key
     * @param string $class
     *
     * @return  mixed
     */
    public function loadAll($key = null, $class = '\\stdClass')
    {
        if (strtolower($class) === 'array') {
            return $this->getReader()->loadArrayList($key);
        }

        if (strtolower($class) === 'assoc') {
            return $this->getReader()->loadAssocList($key);
        }

        return $this->getReader()->loadObjectList($key, $class);
    }

    /**
     * loadOne
     *
     * @param string $class
     *
     * @return  mixed
     */
    public function loadOne($class = '\\stdClass')
    {
        if (strtolower($class) === 'array') {
            return $this->getReader()->loadArray();
        }

        if (strtolower($class) === 'assoc') {
            return $this->getReader()->loadAssoc();
        }

        return $this->getReader()->loadObject($class);
    }

    /**
     * loadResult
     *
     * @return  mixed
     */
    public function loadResult()
    {
        return $this->getReader()->loadResult();
    }

    /**
     * loadColumn
     *
     * @return  mixed
     */
    public function loadColumn()
    {
        return $this->getReader()->loadColumn();
    }

    /**
     * getIndependentQuery
     *
     * @return  Query
     */
    private function getIndependentQuery()
    {
        if (!isset(static::$independentQuery[$this->name])) {
            static::$independentQuery[$this->name] = $this->getQuery(true);
        }

        return static::$independentQuery[$this->name];
    }

    /**
     * quoteName
     *
     * @param string $text
     *
     * @return  mixed
     */
    public function quoteName($text)
    {
        return $this->getIndependentQuery()->quoteName($text);
    }

    /**
     * qn
     *
     * @param string $text
     *
     * @return  mixed
     */
    public function qn($text)
    {
        return $this->quoteName($text);
    }

    /**
     * quote
     *
     * @param string $text
     * @param bool   $escape
     *
     * @return  string
     */
    public function quote($text, $escape = true)
    {
        return $this->getIndependentQuery()->quote($text, $escape);
    }

    /**
     * q
     *
     * @param string $text
     * @param bool   $escape
     *
     * @return  string
     */
    public function q($text, $escape = true)
    {
        return $this->quote($text, $escape);
    }

    /**
     * escape
     *
     * @param string $text
     * @param bool   $extra
     *
     * @return  string
     */
    public function escape($text, $extra = true)
    {
        return $this->getIndependentQuery()->escape($text, $extra);
    }

    /**
     * e
     *
     * @param string $text
     * @param bool   $extra
     *
     * @return  string
     */
    public function e($text, $extra = true)
    {
        return $this->escape($text, $extra);
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Method to get property Options
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Method to set property options
     *
     * @param   array $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Method to set property database
     *
     * @param   string $database
     *
     * @return  static  Return self to support chaining.
     */
    public function setDatabaseName($database)
    {
        $this->database = $database;

        $this->options['database'] = $database;

        return $this;
    }

    /**
     * Method to get property LastQuery
     *
     * @return  string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Find and replace sprintf-like tokens in a format string.
     * Each token takes one of the following forms:
     *     %%       - A literal percent character.
     *     %[t]     - Where [t] is a type specifier.
     *     %[n]$[x] - Where [n] is an argument specifier and [t] is a type specifier.
     *
     * Types:
     * a - Numeric: Replacement text is coerced to a numeric type but not quoted or escaped.
     * e - Escape: Replacement text is passed to $this->escape().
     * E - Escape (extra): Replacement text is passed to $this->escape() with true as the second argument.
     * n - Name Quote: Replacement text is passed to $this->quoteName().
     * q - Quote: Replacement text is passed to $this->quote().
     * Q - Quote (no escape): Replacement text is passed to $this->quote() with false as the second argument.
     * r - Raw: Replacement text is used as-is. (Be careful)
     *
     * Date Types:
     * - Replacement text automatically quoted (use uppercase for Name Quote).
     * - Replacement text should be a string in date format or name of a date column.
     * y/Y - Year
     * m/M - Month
     * d/D - Day
     * h/H - Hour
     * i/I - Minute
     * s/S - Second
     *
     * Invariable Types:
     * - Takes no argument.
     * - Argument index not incremented.
     * t - Replacement text is the result of $this->currentTimestamp().
     * z - Replacement text is the result of $this->nullDate(false).
     * Z - Replacement text is the result of $this->nullDate(true).
     *
     * Usage:
     * $query->format('SELECT %1$n FROM %2$n WHERE %3$n = %4$a', 'foo', '#__foo', 'bar', 1);
     * Returns: SELECT `foo` FROM `#__foo` WHERE `bar` = 1
     *
     * Notes:
     * The argument specifier is optional but recommended for clarity.
     * The argument index used for unspecified tokens is incremented only when used.
     *
     * @param string $format The formatting string.
     * @param array  $args   The parameters.
     *
     * @return  string  Returns a string produced according to the formatting string.
     *
     * @since  3.5
     */
    public function format(string $format, ...$args): string
    {
        return $this->getIndependentQuery()->format($format, ...$args);
    }

    /**
     * getDateFormat
     *
     * @return  string
     *
     * @since   3.2.7
     */
    public function getDateFormat()
    {
        return $this->getIndependentQuery()->getDateFormat();
    }

    /**
     * getNullDate
     *
     * @return  string
     *
     * @since   3.2.7
     */
    public function getNullDate()
    {
        return $this->getIndependentQuery()->getNullDate();
    }

    /**
     * Method to get property Monitor
     *
     * @return  QueryMonitorInterface
     *
     * @since  3.5
     */
    public function getMonitor(): QueryMonitorInterface
    {
        return $this->monitor;
    }

    /**
     * Method to set property monitor
     *
     * @param   QueryMonitorInterface $monitor
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5
     */
    public function setMonitor(QueryMonitorInterface $monitor)
    {
        $this->monitor = $monitor;

        return $this;
    }
}
