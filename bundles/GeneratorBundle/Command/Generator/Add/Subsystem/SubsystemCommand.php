<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Generator\Add\Subsystem;

use GeneratorBundle\Controller\GeneratorController;
use Windwalker\Console\Command\Command;

defined('WINDWALKER') or die;

/**
 * Class Subsystem
 *
 * @since  2.0
 */
class SubsystemCommand extends Command
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
	protected $name = 'subsystem';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected $description = 'Sub system contains item and list two controller to support CRUD a table.';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected $usage = 'subsystem <cmd><command></cmd> <option>[option]</option>';

	/**
	 * Configure command information.
	 *
	 * @return void
	 */
	public function configure()
	{
		// $this->addArgument();

		parent::configure();
	}

	/**
	 * Execute this command.
	 *
	 * @return int|void
	 */
	protected function doExecute()
	{
		$generator = new GeneratorController($this);

		$generator->setTask('add.subsystem')->execute();
	}
}
