<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Query\Mysql;

use Windwalker\Query\Query;

/**
 * Class MysqlQuery
 *
 * @since {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	protected $nullDate = '0000-00-00 00:00:00';

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

