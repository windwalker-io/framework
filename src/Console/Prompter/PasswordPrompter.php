<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Prompter;

/**
 * A password prompter supports hidden input.
 *
 * @since  {DEPLOY_VERSION}
 */
class PasswordPrompter extends CallbackPrompter
{
	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $noValidMessage = '  Not a valid password.';

	/**
	 * Which shell we use.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected static $shell;

	/**
	 * Is stty available?
	 *
	 * @var boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected static $stty;

	/**
	 * Is Windows OS?
	 *
	 * @var  boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $win = false;

	/**
	 * The Hidden input exe poath for Windows OS.
	 *
	 * @see https://github.com/Seldaek/hidden-input
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $hiddenExe = null;

	/**
	 * Initialise this class.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->win = defined('PHP_WINDOWS_VERSION_BUILD');

		$this->hiddenExe = __DIR__ . '/../bin/hiddeninput.exe';

		// Default handler
		$closure = function($value)
		{
			return (bool) $value;
		};

		$this->setHandler($closure);
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
	public function ask($msg = '', $default = null)
	{
		return parent::ask($msg, $default);
	}

	/**
	 * Get a value from standard input.
	 *
	 * @param   string  $question  The question you want to ask user.
	 *
	 * @throws  \RuntimeException
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function in($question = '')
	{
		$question = $question ? : $this->question;

		if ($this->win)
		{
			if ($question)
			{
				$this->io->out()->out($question, false);
			}

			$value = rtrim(shell_exec($this->hiddenExe));

			$this->io->out();

			return $value;
		}

		// Using stty help us test this class.
		elseif ($this->findStty())
		{
			if ($question)
			{
				$this->io->out()->out($question, false);
			}

			// Get stty setting
			$setting = shell_exec('stty -g');

			shell_exec('stty -echo');

			$value = fread($this->inputStream, 8192);

			shell_exec(sprintf('stty %s', $setting));

			if ($value === false)
			{
				throw new \RuntimeException('Cannot get input value.');
			}

			$this->io->out();

			return rtrim($value);
		}

		// For linux & Unix system
		else
		{
			// Find shell.
			$shell = $this->findShell();

			if (!$shell)
			{
				throw new \RuntimeException("Can't invoke shell");
			}

			$this->io->out();

			// Using read to write password
			$read = sprintf('read -s -p "%s" mypassword && echo $mypassword', $question);

			// Here we use bash to handle this command.
			$command = sprintf("/usr/bin/env bash -c '%s'", $read);

			$value = rtrim(shell_exec($command));

			$this->io->out();

			return $value;
		}
	}

	/**
	 * Find which shell we use (only in UNIX & LINUX).
	 *
	 * @return  string  Shell name.
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function findShell()
	{
		if (self::$shell)
		{
			return self::$shell;
		}

		$command = "/usr/bin/env %s -c 'echo Hello'";

		foreach (array('bash', 'zsh', 'ksh', 'csh') as $shell)
		{
			if (rtrim(shell_exec(sprintf($command, $shell))) === 'Hello')
			{
				self::$shell = $shell;

				return $shell;
			}
		}

		return null;
	}

	/**
	 * Find stty (only in UNIX & LINUX).
	 *
	 * @return  boolean  Stty exists or not.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function findStty()
	{
		if (null !== self::$stty)
		{
			return self::$stty;
		}

		exec('stty 2>&1', $output, $exitcode);

		return self::$stty = ($exitcode === 0);
	}
}
