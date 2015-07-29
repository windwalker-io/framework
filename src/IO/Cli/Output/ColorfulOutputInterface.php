<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Cli\Output;

use Windwalker\IO\Cli\Color\ColorProcessorInterface;

/**
 * The ColorfulOutputInterface class.
 * 
 * @since  2.0
 */
interface ColorfulOutputInterface
{
	/**
	 * Set a processor
	 *
	 * @param   ColorProcessorInterface  $processor  The output processor.
	 *
	 * @return  CliOutput  Instance of $this to allow chaining.
	 *
	 * @since   2.0
	 */
	public function setProcessor(ColorProcessorInterface $processor);

	/**
	 * Get a processor
	 *
	 * @return  ColorProcessorInterface
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function getProcessor();
}
