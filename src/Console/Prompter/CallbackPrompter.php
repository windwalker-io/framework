<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Prompter;

/**
 * Callback validate prompter.
 *
 * It supports custom callback to validate use input and retry if fail.
 *
 * @since  {DEPLOY_VERSION}
 */
class CallbackPrompter extends AbstractPrompter
{
	/**
	 * The callable handler.
	 *
	 * @var  callable
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $handler = null;

	/**
	 * Retry times.
	 *
	 * @var  int
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $attempt = 3;

	/**
	 * If this property set to true, application will be closed when validate fail.
	 *
	 * @var  boolean
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $failToClose = false;

	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $noValidMessage = '  Not a valid value.';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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

