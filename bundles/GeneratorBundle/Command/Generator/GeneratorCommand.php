<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Generator;

use Joomla\Console\Option\Option;
use Windwalker\Console\Command\Command;
use Windwalker\DI\Container;

defined('WINDWALKER') or die;

/**
 * Class Genarator
 *
 * @since  2.0
 */
class GeneratorCommand extends Command
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
	protected $name = 'generator';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected $description = 'Extension generator.';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected $usage = 'generator <cmd><command></cmd> <option>[option]</option>';

	/**
	 * Configure command information.
	 *
	 * @return void
	 */
	public function configure()
	{
		parent::configure();

		$this->addOption(
			array('c', 'client'),
			null,
			'Site or administrator (admin)',
			Option::IS_GLOBAL
		)
		->addOption(
			array('t', 'tmpl'),
			'default',
			'Using template.',
			Option::IS_GLOBAL
		);
	}

	/**
	 * Execute this command.
	 *
	 * @return int|void
	 */
	protected function doExecute()
	{
		return parent::doExecute();
	}
}
