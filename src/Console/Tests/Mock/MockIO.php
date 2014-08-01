<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Tests\Mock;

use Windwalker\IO\Cli\Input\CliInputInterface;
use Windwalker\IO\Cli\IO;
use Windwalker\IO\Cli\Output\CliOutputInterface;

/**
 * The MockIO class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class MockIO extends IO
{
	/**
	 * Class init.
	 *
	 * @param CliInputInterface  $input
	 * @param CliOutputInterface $output
	 */
	public function __construct(CliInputInterface $input = null, CliOutputInterface $output = null)
	{
		parent::__construct($input, new MockOutput);
	}

	/**
	 * getOutputStream
	 *
	 * @return  mixed
	 */
	public function getOutputStream()
	{
		return $this->output->output;
	}

	/**
	 * setOutput
	 *
	 * @param CliOutputInterface $output
	 *
	 * @return  static
	 */
	public function setOutput(CliOutputInterface $output)
	{
		return $this;
	}
}
