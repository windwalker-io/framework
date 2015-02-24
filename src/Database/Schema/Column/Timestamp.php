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
 * The TIMESTAMP class.
 * 
 * @since  2.0
 */
class Timestamp extends Column
{
	/**
	 * Class init.
	 *
	 * @param string $name
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 */
	public function __construct($name = null, $allowNull = false, $default = '', $comment = '', $options = array())
	{
		parent::__construct($name, DataType::TIMESTAMP, true, $allowNull, $default, $comment, $options);
	}
}
