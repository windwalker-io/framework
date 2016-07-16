<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Console\Prompter;

/**
 * The Prompter class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Prompter
{
	/**
	 * boolean
	 *
	 * @param string $msg
	 * @param mixed  $default
	 *
	 * @return  boolean
	 */
	public static function boolean($msg = '', $default = null)
	{
		$prompter = new BooleanPrompter;

		return $prompter->ask($msg, $default);
	}

	/**
	 * text
	 *
	 * @param string  $msg
	 * @param mixed   $default
	 *
	 * @return  string
	 */
	public static function text($msg = '', $default = null)
	{
		$prompter = new TextPrompter;

		return $prompter->ask($msg, $default);
	}

	/**
	 * callback
	 *
	 * @param string   $msg
	 * @param callable $handler
	 * @param mixed    $default
	 * @param string   $noValidMessage
	 * @param int      $attemptTimes
	 * @param bool     $failCloseMessage
	 *
	 * @return  string
	 */
	public static function callback($msg = '', $handler = null, $default = null, $noValidMessage = null, $attemptTimes = 3,
		$failCloseMessage = false)
	{
		$prompter = new CallbackPrompter;

		$prompter->setHandler($handler);

		static::prepareCallbackPrompter($prompter, $noValidMessage, $attemptTimes, $failCloseMessage);

		return $prompter->ask($msg, $default);
	}

	/**
	 * validText
	 *
	 * @param string $msg
	 * @param array  $options
	 * @param mixed  $default
	 * @param string $noValidMessage
	 * @param int    $attemptTimes
	 * @param bool   $failCloseMessage
	 *
	 * @return  string
	 */
	public static function validText($msg = '', $options = array(), $default = null, $noValidMessage = null, $attemptTimes = 3,
		$failCloseMessage = false)
	{
		$prompter = new ValidatePrompter;

		$prompter->setOptions($options);

		static::prepareCallbackPrompter($prompter, $noValidMessage, $attemptTimes, $failCloseMessage);

		return $prompter->ask($msg, $default);
	}

	/**
	 * notNullText
	 *
	 * @param string $msg
	 * @param mixed  $default
	 * @param string $noValidMessage
	 * @param int    $attemptTimes
	 * @param bool   $failCloseMessage
	 *
	 * @return  string
	 */
	public static function notNullText($msg = '', $default = null, $noValidMessage = null, $attemptTimes = 3,
		$failCloseMessage = false)
	{
		$prompter = new NotNullPrompter;

		static::prepareCallbackPrompter($prompter, $noValidMessage, $attemptTimes, $failCloseMessage);

		return $prompter->ask($msg, $default);
	}

	/**
	 * selector
	 *
	 * @param string $msg
	 * @param array  $options
	 * @param mixed  $default
	 * @param string $noValidMessage
	 * @param int    $attemptTimes
	 * @param bool   $failCloseMessage
	 *
	 * @return  string
	 */
	public static function selector($msg = '', $options = array(), $default = null, $noValidMessage = null, $attemptTimes = 3,
		$failCloseMessage = false)
	{
		$prompter = new SelectPrompter;

		$prompter->setOptions($options);

		static::prepareCallbackPrompter($prompter, $noValidMessage, $attemptTimes, $failCloseMessage);

		return $prompter->ask($msg, $default);
	}

	/**
	 * password
	 *
	 * @param string $msg
	 * @param mixed  $default
	 * @param string $noValidMessage
	 * @param int    $attemptTimes
	 * @param bool   $failCloseMessage
	 *
	 * @return  string
	 */
	public static function password($msg, $default = null, $noValidMessage = null, $attemptTimes = 3,
		$failCloseMessage = false)
	{
		$prompter = new PasswordPrompter;

		static::prepareCallbackPrompter($prompter, $noValidMessage, $attemptTimes, $failCloseMessage);

		return $prompter->ask($msg, $default);
	}

	/**
	 * prepareCallbackPrompter
	 *
	 * @param CallbackPrompter $prompter
	 * @param string|null      $noValidMessage
	 * @param integer          $attemptTimes
	 * @param string|null      $failCloseMessage
	 *
	 * @return CallbackPrompter
	 */
	protected static function prepareCallbackPrompter(CallbackPrompter $prompter, $noValidMessage, $attemptTimes, $failCloseMessage)
	{
		$prompter->setAttemptTimes($attemptTimes);

		if ($noValidMessage !== null)
		{
			$prompter->setNoValidMessage($noValidMessage);
		}

		if ($failCloseMessage !== false)
		{
			$prompter->failToClose(true, $failCloseMessage);
		}

		return $prompter;
	}
}
