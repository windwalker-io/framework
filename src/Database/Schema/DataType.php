<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Schema;

/**
 * The ColumnType class.
 *
 * The types data were referenced from:
 * https://docs.google.com/document/d/168GnMgXb8afOby1n9iLQXzu-PWujs-HxTv5YbEvmu-4/edit
 * 
 * @since  2.0
 */
abstract class DataType
{
	// BOOLEAN
	const BOOLEAN = 'boolean';

	// CHARACTER
	const CHAR = 'char';
	const VARCHAR = 'varchar';

	// BIT
	const BIT = 'bit';
	const BIT_VARYING = 'bit varying';

	// EXACT NUMERIC
	const INTEGER = 'integer';
	const SMALLINT = 'smallint';
	const DECIMAL = 'decimal';
	const NUMERIC = 'numeric';

	// APPROXIMATE NUMERIC
	const FLOAT = 'float';
	const REAL = 'real';
	const DOUBLE = 'double';

	// DATETIME
	const DATE = 'date';
	const TIME = 'time';
	const TIMESTAMP = 'timestamp';

	// INTERVAL
	const INTERVAL = 'interval';

	// LARGE OBJECTS
	const CHARACTER = 'character';
	const LARGE = 'large';
	const OBJECT_BINARY = 'objectbinary';
	const LARGE_OBJECT = 'large object';

	// Not SQL92 types but common
	const TEXT = 'text';
	const LONGTEXT = 'longtext';
	const TINYINT = 'tinyint';
	const DATETIME = 'datetime';

	/**
	 * Property typeMapping.
	 *
	 * @var  array
	 */
	protected static $typeMapping = array();

	/**
	 * Property types.
	 *
	 * @var  array
	 */
	public static $defaultLengths = array(
		// VARCHAR
		self::VARCHAR => 255,
		self::CHAR => 255,

		// NUMERIC
		self::INTEGER => 11,
		self::TINYINT => 4,
		self::SMALLINT => 6,
		self::BIT => 1,
		self::DECIMAL => '10,2',
		self::DOUBLE => '10,2',
		self::FLOAT => '10,2',
	);

	/**
	 * getLength
	 *
	 * @param   string  $type
	 *
	 * @return  integer
	 */
	public static function getLength($type)
	{
		$type = strtolower($type);

		if (array_key_exists($type, static::$defaultLengths))
		{
			return static::$defaultLengths[$type];
		}

		if (array_key_exists($type, self::$defaultLengths))
		{
			return self::$defaultLengths[$type];
		}

		return null;
	}

	/**
	 * getType
	 *
	 * @param   string  $type
	 *
	 * @return  string
	 */
	public static function getType($type)
	{
		$type = strtolower($type);

		if (!isset(static::$typeMapping[$type]))
		{
			return $type;
		}

		return static::$typeMapping[$type];
	}
}
