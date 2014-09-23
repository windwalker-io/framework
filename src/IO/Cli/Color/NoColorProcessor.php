<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
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
	 * @since  {DEPLOY_VERSION}
	 */
	public $noColors = true;
}
