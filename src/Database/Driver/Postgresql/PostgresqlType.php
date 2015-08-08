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
		'tinytext' => self::TEXT,
		'mediumtext' => self::TEXT,
		DataType::LONGTEXT => self::TEXT,
		// MysqlType::ENUM => self::VARCHAR, // Postgres support ENUM after 8.3
		MysqlType::SET => self::TEXT
	);

	/**
	 * Property noLength.
	 *
	 * @var  array
	 */
	protected static $noLength = array(
		self::INTEGER,
		self::SMALLINT,
		self::SERIAL
	);

	/**
	 * noLength
	 *
	 * @param   string $type
	 *
	 * @return  boolean
	 */
	public static function noLength($type)
	{
		$type = strtolower($type);

		return in_array($type, static::$noLength);
	}
}
