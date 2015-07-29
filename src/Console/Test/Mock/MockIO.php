<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Mock;

use Windwalker\Console\IO\IO;
use Windwalker\IO\Cli\Input\CliInputInterface;
use Windwalker\IO\Cli\Output\CliOutputInterface;

/**
 * The MockIO class.
 * 
 * @since  2.0
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
	public function getTestOutput()
	{
		return $this->output->output;
	}

	/**
	 * getOutputStream
	 *
	 * @return  mixed
	 */
	public function setTestOutput($output)
	{
		return $this->output->output = $output;
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
