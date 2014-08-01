<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\IO\Cli\Color;

/**
 * The NullColorProcessor class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class NoColorProcessor extends ColorProcessor
{
	/**
	 * Flag to remove color codes from the output
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $noColors = true;
}
