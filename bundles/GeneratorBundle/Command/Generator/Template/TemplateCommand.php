<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Generator\Template;

use Windwalker\Console\Command\Command;

defined('WINDWALKER') or die;

/**
 * Class Template
 *
 * @since  2.0
 */
class TemplateCommand extends Command
{
	/**
	 * An enabled flag.
	 *
	 * @var bool
	 */
	public static $isEnabled = true;

	/**
	 * Console(Argument) name.
	 *
	 * @var  string
	 */
	protected $name = 'template';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected $description = 'template';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected $usage = 'template <cmd><command></cmd> <option>[option]</option>';
}
