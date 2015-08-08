<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Driver\Mysql\MysqlType;
use Windwalker\Database\Schema\DataType;

/**
 * The PostgresqlType class.
 * 
 * @since  2.1
 */
abstract class PostgresqlType extends DataType
{
	const INTEGER = 'integer';
	const BOOLEAN = 'bool';
	const SERIAL = 'serial';

	/**
	 * Property types.
	 *
	 * @var  array
	 */
	public static $defaultLengths = array(
		self::INTEGER => null,
		self::SERIAL  => null,
		self::SMALLINT  => null,
	);

	/**
	 * Property typeMapping.
	 *
	 * @see  https://en.wikibooks.org/wiki/Converting_MySQL_to_PostgreSQL
	 *
	 * @var  array
	 */
	protected static $typeMapping = array(
		DataType::TINYINT  => self::SMALLINT,
		DataType::DATETIME => self::TIMESTAMP,
		'TINYTEXT' => self::TEXT,
		'MEDIUMTEXT' => self::TEXT,
		DataType::LONGTEXT => self::TEXT,
		// MysqlType::ENUM => self::VARCHAR, // Postgres support ENUM after 8.3
		MysqlType::SET => self::TEXT
	);
}
