<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

use Windwalker\Console\IO\IO;
use Windwalker\Console\IO\IOInterface;

/**
 * Prompter class.
 *
 * Help us show dialog to ask use questions.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractPrompter implements PrompterInterface
{
	/**
	 * Property question.
	 *
	 * @var  string
	 */
	protected $question = null;

	/**
	 * The input object.
	 *
	 * @var  IOInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $io = null;

	/**
	 * The default value.
	 *
	 * @var  mixed
	 */
	protected $default = null;

	/**
	 * Input stream, default is STDIN.
	 *
	 * Replace this resource help us easily test this class.
	 *
	 * @var  resource
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $inputStream = STDIN;

	/**
	 * Constructor.
	 *
	 * @param   string       $question  The question you want to ask.
	 * @param   $default     $default   The default value.
	 * @param   IOInterface  $io        The input object.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	function __construct($question = null, $default = null, IOInterface $io = null)
	{
		$this->io = $io ? : new IO;
		$this->question = $question;
		$this->default  = $default;

		$this->preprocess();
	}

	/**
	 * Method to initialise something customize.
	 *
	 * @return  void
	 */
	protected function preprocess()
	{
		// Override this method to initialise something.
	}

	/**
	 * Show prompt to ask user.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	abstract public function ask($msg = '', $default = '');

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function in($question = null)
	{
		$question = $question ? : $this->question;

		if ($question)
		{
			$this->io->out()->out($question, false);
		}

		$value = $this->io->in();

		return $value;
	}

	/**
	 * Proxy to ask method.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __invoke($msg = null, $default = null)
	{
		return $this->ask($msg, $default);
	}

	/**
	 * Method to get property Io
	 *
	 * @return  \Windwalker\Console\IO\IOInterface
	 */
	public function getIO()
	{
		return $this->io;
	}

	/**
	 * Method to set property io
	 *
	 * @param   \Windwalker\Console\IO\IOInterface $io
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setIO($io)
	{
		$this->io = $io;

		return $this;
	}
}
