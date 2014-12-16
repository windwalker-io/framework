<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Schema\DataType;

/**
 * The MysqlType class.
 * 
 * @since  2.0
 */
abstract class MysqlType extends DataType
{
	const INTEGER = 'int';
	const BOOLEAN = 'bool';
	const ENUM = 'enum';

	/**
	 * Property types.
	 *
	 * @var  array
	 */
	public static $defaultLengths = array(
		self::INTEGER => 11,
	);

	/**
	 * Property typeMapping.
	 *
	 * @var  array
	 */
	protected static $typeMapping = array(
		DataType::INTEGER => 'int',
		DataType::BIT     => self::TINYINT,
		DataType::BOOLEAN => self::BOOLEAN
	);
}
