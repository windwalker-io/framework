<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Schema\DataType;

/**
 * The MysqlType class.
 * 
 * @since  2.0
 */
class MysqlType extends DataType
{
	const INTEGER = 'int';
	const BOOLEAN = 'bool';
	const ENUM = 'enum';
	const SET = 'set';

	/**
	 * Property types.
	 *
	 * @var  array
	 */
	public static $defaultLengths = array(
		self::INTEGER => 11,
	);

	/**
	 * "Length", "Default", "PHP Type"
	 *
	 * @var  array
	 */
	public static $typeDefinitions = array(
		self::BOOLEAN  => array(1,  0, 'boolean'),
		self::INTEGER  => array(11, 0, 'integer'),
		self::ENUM     => array(null,  '', 'string'),
		self::SET      => array(null,  '', 'string'),
	);

	/**
	 * Property typeMapping.
	 *
	 * @var  array
	 */
	protected static $typeMapping = array(
		DataType::INTEGER => 'int',
		DataType::BIT     => self::TINYINT,
		DataType::BOOLEAN => self::BOOLEAN,
	);
}
