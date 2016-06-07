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
class DataType
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
	 * "Default Length", "Default Value", "PHP Type"
	 *
	 * @var  array
	 */
	public static $typeDefinitions = array(
		self::BOOLEAN => array(1, 0, 'boolean'),

		self::CHAR     => array(255, '', 'string'),
		self::VARCHAR  => array(255, '', 'string'),
		self::TEXT     => array(null, '', 'string'),
		self::LONGTEXT => array(null, '', 'string'),

		self::BIT         => array(1, 0, 'integer'),
		self::BIT_VARYING => array(1, 0, 'integer'),

		self::INTEGER  => array(11, 0, 'integer'),
		self::SMALLINT => array(6,  0, 'integer'),
		self::TINYINT  => array(4,  0, 'integer'),
		self::NUMERIC  => array(10, 0, 'integer'),

		self::DECIMAL => array('10,2', 0, 'float'),
		self::FLOAT   => array('10,2', 0, 'float'),
		self::REAL    => array('10,2', 0, 'float'),
		self::DOUBLE  => array('10,2', 0, 'float'),

		self::DATE => array(null, '0000-00-00', 'string'),
		self::TIME => array(null, '00:00:00', 'string'),
		self::TIMESTAMP => array(null, '0', 'string'),
		self::DATETIME  => array(null, '0000-00-00 00:00:00', 'string'),
	);

	/**
	 * Property instances.
	 *
	 * @var  static[]
	 */
	protected static $instances = array();

	/**
	 * getInstance
	 *
	 * @param   string  $driver
	 *
	 * @return  static
	 */
	public static function getInstance($driver)
	{
		$driver = ucfirst($driver);

		if (!isset(static::$instances[$driver]))
		{
			$class = sprintf('Windwalker\Database\Driver\%s\%sType', $driver, $driver);

			static::$instances[$driver] = new $class;
		}

		return static::$instances[$driver];
	}

	/**
	 * getLength
	 *
	 * @param   string  $type
	 *
	 * @return  integer
	 */
	public static function getLength($type)
	{
		return static::getProfile($type, 0);
	}

	/**
	 * getDefaultValue
	 *
	 * @param   string  $type
	 *
	 * @return  string
	 */
	public static function getDefaultValue($type)
	{
		return static::getProfile($type, 1);
	}

	/**
	 * getPhpType
	 *
	 * @param   string  $type
	 *
	 * @return  string
	 */
	public static function getPhpType($type)
	{
		return static::getProfile($type, 2);
	}

	/**
	 * getProfile
	 *
	 * @param string  $type
	 * @param integer $key
	 *
	 * @return  string
	 */
	protected static function getProfile($type, $key = null)
	{
		$type = strtolower($type);

		if (array_key_exists($type, static::$typeDefinitions))
		{
			return static::$typeDefinitions[$type][$key];
		}

		if (array_key_exists($type, self::$typeDefinitions))
		{
			return self::$typeDefinitions[$type][$key];
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
