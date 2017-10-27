<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Schema\Column;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;

/**
 * The DATE class.
 * 
 * @since  2.0
 */
class Date extends Column
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
	public function __construct($name = null, $allowNull = false, $default = null, $comment = '', $options = [])
	{
		parent::__construct($name, DataType::DATE, true, $allowNull, $default, $comment, $options);
	}
}
