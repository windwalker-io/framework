<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Toolbar;

use JToolbar;

/**
 * Class Button
 *
 * @since 1.0
 */
class Button
{
	private $name;
	private $type;
	private $code;
	private $access;

	/**
	 * @param $name
	 * @param $type
	 * @param $code
	 * @param $access
	 */
	public function __construct($name, $type = null, $code = null, $access = null)
	{

		$this->name = $name;
		$this->type = $type;
		$this->code = $code;
		$this->access = $access;
	}
}
