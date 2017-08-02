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
 * The Varchar class.
 * 
 * @since  2.0
 */
class Longtext extends Column
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
	public function __construct($name = null, $allowNull = false, $default = false, $comment = '', $options = [])
	{
		parent::__construct($name, DataType::LONGTEXT, Column::SIGNED, $allowNull, $default, $comment, $options);
	}
}
