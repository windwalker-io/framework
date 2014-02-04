<?php

namespace SystemBundle\Command\Build;

use Windwalker\Console\Command\Command;

/**
 * Class BuildCommand
 *
 * @since 1.0
 */
class BuildCommand extends Command
{
	/**
	 * An enabled flag.
	 *
	 * @var bool
	 */
	public static $isEnabled = true;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'build';

	/**
	 * Property description.
	 *
	 * @var  string
	 */
	protected $description = 'Some useful tools for building system.';

	/**
	 * configure
	 *
	 * @return  void
	 */
	public function configure()
	{
		parent::configure();
	}
}
