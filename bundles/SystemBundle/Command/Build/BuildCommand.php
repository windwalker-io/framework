<?php

namespace SystemBundle\Command\Build;

use Windwalker\Console\Command\Command;

class BuildCommand extends Command
{
	/**
	 * An enabled flag.
	 *
	 * @var bool
	 */
	public static $isEnabled = true;

	protected $name = 'build';

	protected $description = 'Some useful tools for building system.';

	public function configure()
	{
		parent::configure();
	}
}
