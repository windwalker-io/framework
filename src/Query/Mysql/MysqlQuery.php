<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\Query;

/**
 * Class MysqlQuery
 *
 * @since 2.0
 */
class MysqlQuery extends Query
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'mysql';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * Class constructor.
	 *
	 * @param   \PDO $connection The PDO connection object to help us escape string.
	 *
	 * @since   2.0
	 */
	public function __construct(\PDO $connection = null)
	{
		parent::__construct($connection);

		if ($this->connection instanceof \PDO &&
			version_compare($this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.7', '>='))
		{
			$this->nullDate = '1000-01-01 00:00:00';
		}
	}

	/**
	 * If no connection set, we escape it with default function.
	 *
	 * Since mysql_real_escape_string() has been deprecated, we use an alternative one.
	 * Please see: http://stackoverflow.com/questions/4892882/mysql-real-escape-string-for-multibyte-without-a-connection
	 *
	 * @param string $text
	 *
	 * @return  string
	 */
	protected function escapeWithNoConnection($text)
	{
		return str_replace(
			array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
			array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
			$text
		);
	}
}
