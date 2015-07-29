<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Prompter;

/**
 * Callback validate prompter.
 *
 * It supports custom callback to validate use input and retry if fail.
 *
 * @since  2.0
 */
class CallbackPrompter extends AbstractPrompter
{
	/**
	 * The callable handler.
	 *
	 * @var  callable
	 *
	 * @since  2.0
	 */
	protected $handler = null;

	/**
	 * Retry times.
	 *
	 * @var  int
	 *
	 * @since  2.0
	 */
	protected $attempt = 3;

	/**
	 * If this property set to true, application will be closed when validate fail.
	 *
	 * @var  boolean
	 *
	 * @since  2.0
	 */
	protected $failToClose = false;

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $noValidMessage = '  Not a valid value.';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  2.0
	 */
	protected $closeMessage = '  Valid fail and close.';

	/**
	 * Show prompt to ask user.
	 *
	 * @param   string  $msg      Question.
	 * @param   string  $default  Default value.
	 *
	 * @throws  \LogicException
	 *
	 * @return  string  The value that use input.
	 *
	 * @since   2.0
	 */
	public function ask($msg = '', $default = null)
	{
		for ($i = 1; $i <= $this->attempt; $i++)
		{
			$value = trim($this->in($msg));

			$handler = $this->getHandler();

			if (!is_callable($handler))
			{
				throw new \LogicException('Please set a callable handler first.');
			}

			if ((boolean) call_user_func($this->getHandler(), $value))
			{
				return $value;
			}

			$this->io->out($this->noValidMessage);
		}

		if ($this->failToClose)
		{
			$this->io->out()->out($this->closeMessage);

			die;
		}

		$default = $default ? : $this->default;

		return $default;
	}

	/**
	 * Set a callable handler, can be a Closure.
	 *
	 * This function should contain a param that is the value which from user input,
	 * and must return TRUE or FALSE means validate success or fail.
	 *
	 * @param   callable  $handler  The validate callback.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   2.0
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Set attempt number.
	 *
	 * @param   int  $attempt  Retry times.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function setAttempt($attempt)
	{
		$this->attempt = $attempt;

		return $this;
	}

	/**
	 * Set message when validate fail.
	 *
	 * @param   string   $noValidMessage  Validate fail message.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function setNoValidMessage($noValidMessage)
	{
		$this->noValidMessage = $noValidMessage;

		return $this;
	}

	/**
	 * If validate fail, whether close application or not.
	 *
	 * @param   boolean  $failToClose  TRUE or FALSE, if is NULL, will be getter of $failToClose property.
	 * @param   string   $message      Message when close.
	 *
	 * @return  ValidatePrompter  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function failToClose($failToClose = null, $message = '')
	{
		if (is_null($failToClose))
		{
			return $this->failToClose;
		}

		$this->failToClose  = $failToClose;
		$this->closeMessage = $message ? $message : $this->closeMessage;

		return $this;
	}
}

