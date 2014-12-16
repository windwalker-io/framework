<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Descriptor;

use Windwalker\Console\Command\AbstractCommand;

/**
 * A descriptor helper to get different descriptor and render it.
 *
 * @since  2.0
 */
interface DescriptorHelperInterface
{
	/**
	 * Describe a command detail.
	 *
	 * @param   AbstractCommand  $command  The command to described.
	 *
	 * @return  string  Return the described text.
	 *
	 * @since   2.0
	 */
	public function describe(AbstractCommand $command);
}
