<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Cli\Color;

/**
 * Class ProcessorInterface.
 *
 * @since  1.1.0
 */
interface ColorProcessorInterface
{
	/**
	 * Process the provided output into a string.
	 *
	 * @param   string $output
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function process($output);

	/**
	 * Add a style.
	 *
	 * @param   string      $name   The style name.
	 * @param   ColorStyle  $style  The color style.
	 *
	 * @return  ColorProcessor  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function addStyle($name, ColorStyle $style);
}
