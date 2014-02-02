<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Generator;

use Windwalker\Console\Command\Command;

/**
 * Class GeneratorCommand
 *
 * @since 1.0
 */
class GeneratorCommand extends Command
{
	/**
	 * Property isEnabled.
	 *
	 * @var  bool
	 */
	public static $isEnabled = true;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'generator';

	/**
	 * Property description.
	 *
	 * @var  string
	 */
	protected $description = 'Component generator';
}
