<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Prompter;

use Windwalker\Console\IO\IOFactory;
use Windwalker\Console\IO\IOInterface;

/**
 * Prompter class.
 *
 * Help us show dialog to ask use questions.
 *
 * @since  2.0
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
	 * @since  2.0
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
	 * @since  2.0
	 */
	protected $inputStream = STDIN;

	/**
	 * Constructor.
	 *
	 * @param   string       $question  The question you want to ask.
	 * @param   $default     $default   The default value.
	 * @param   IOInterface  $io        The input object.
	 *
	 * @since   2.0
	 */
	function __construct($question = null, $default = null, IOInterface $io = null)
	{
		$this->io = $io ? : IOFactory::getIO();
		$this->question = $question;
		$this->default  = $default;

		$this->initialise();
		$this->preprocess();
	}

	/**
	 * Method to initialise something customize.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		// Override this method to initialise something.
	}

	/**
	 * Method to initialise something customize.
	 *
	 * @deprecated Use initialise() instead.
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
	 * @since   2.0
	 */
	abstract public function ask($msg = '', $default = '');

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   2.0
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
	 * @since   2.0
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
