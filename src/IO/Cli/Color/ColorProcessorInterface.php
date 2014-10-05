<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\IO\Cli\Color;

/**
 * Class ProcessorInterface.
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function addStyle($name, ColorStyle $style);

	/**
	 * Method to set property noColors
	 *
	 * @param   boolean $noColors
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setNoColors($noColors);
}
