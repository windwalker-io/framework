<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\IO\Cli\Output;

use Windwalker\IO\Cli\Color\ColorProcessorInterface;

/**
 * The ColorfulOutputInterface class.
 * 
 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function setProcessor(ColorProcessorInterface $processor);

	/**
	 * Get a processor
	 *
	 * @return  ColorProcessorInterface
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \RuntimeException
	 */
	public function getProcessor();
}
