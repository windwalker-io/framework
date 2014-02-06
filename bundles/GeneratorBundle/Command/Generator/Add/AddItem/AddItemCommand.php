<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Generator\Add\AddItem;

use GeneratorBundle\Controller\GeneratorController;
use Windwalker\Console\Command\Command;

defined('WINDWALKER') or die;

/**
 * Class Item
 *
 * @since  2.0
 */
class AddItemCommand extends Command
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
	protected $name = 'item';

	/**
	 * The command description.
	 *
	 * @var  string
	 */
	protected $description = 'Add a singular MVC group for item CRUD.';

	/**
	 * The usage to tell user how to use this command.
	 *
	 * @var string
	 */
	protected $usage = 'item <cmd><command></cmd> <option>[option]</option>';

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

		$generator->setTask('add.item')->execute();
	}
}
