<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\IO;

use CodeGenerator\IO\IOInterface;
use Windwalker\Console\Application\Console;
use Windwalker\Console\Command\Command;
use Windwalker\DI\Container;

/**
 * Class IO
 *
 * @since 1.0
 */
class IO implements IOInterface
{
	/**
	 * Property command.
	 *
	 * @var Command
	 */
	protected $command;

	/**
	 * @param Command $command
	 */
	public function __construct(Command $command)
	{
		$this->command = $command;
	}

	/**
	 * out
	 *
	 * @param string $msg
	 *
	 * @return  $this
	 */
	public function out($msg = '')
	{
		$this->command->out($msg);

		return $this;
	}

	/**
	 * in
	 *
	 * @param string $question
	 *
	 * @return  string|null
	 */
	public function in($question = '')
	{
		return $this->command->in($question);
	}

	/**
	 * err
	 *
	 * @param string $msg
	 *
	 * @return  $this
	 */
	public function err($msg = '')
	{
		$this->command->err($msg);

		return $this;
	}

	/**
	 * close
	 *
	 * @param string $msg
	 *
	 * @return  void
	 */
	public function close($msg = '')
	{
		$this->command->close($msg);
	}

	/**
	 * getCommand
	 *
	 * @return  mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * setCommand
	 *
	 * @param   Command $command
	 *
	 * @return  IO  Return self to support chaining.
	 */
	public function setCommand(Command $command)
	{
		$this->command = $command;

		return $this;
	}

	/**
	 * getArgument
	 *
	 * @param string $offset
	 * @param string $default
	 *
	 * @return  mixed
	 */
	public function getArgument($offset, $default = null)
	{
		return $this->command->getArgument($offset, $default);
	}

	/**
	 * getOption
	 *
	 * @param string $name
	 * @param string $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		return $this->command->getOption($name, $default);
	}
}
