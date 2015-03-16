<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Schema\Column;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;

/**
 * The Decimal class.
 * 
 * @since  2.0
 */
class Decimal extends Column
{
	/**
	 * Class init.
	 *
	 * @param string $name
	 * @param string $length
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 */
	public function __construct($name = null, $length = '10,2', $signed = false, $allowNull = false, $default = null, $comment = '', $options = array())
	{
		$options['length'] = $length;

		parent::__construct($name, DataType::DECIMAL, $signed, $allowNull, $default, $comment, $options);
	}
}
