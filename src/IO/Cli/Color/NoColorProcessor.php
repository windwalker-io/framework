<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Cli\Color;

/**
 * The NullColorProcessor class.
 * 
 * @since  2.0
 */
class NoColorProcessor extends ColorProcessor
{
	/**
	 * Flag to remove color codes from the output
	 *
	 * @var    boolean
	 * @since  2.0
	 */
	public $noColors = true;
}
