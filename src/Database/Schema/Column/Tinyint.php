<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Schema\Column;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;

/**
 * The Varchar class.
 * 
 * @since  2.0
 */
class Tinyint extends Column
{
	/**
	 * Class init.
	 *
	 * @param string $name
	 * @param int    $length
	 * @param bool   $signed
	 * @param bool   $allowNull
	 * @param string $default
	 * @param string $comment
	 * @param array  $options
	 */
	public function __construct($name = null, $length = 3, $signed = false, $allowNull = false, $default = null, $comment = '', $options = array())
	{
		$options['length'] = $length;

		parent::__construct($name, DataType::TINYINT, $signed, $allowNull, $default, $comment, $options);
	}
}
