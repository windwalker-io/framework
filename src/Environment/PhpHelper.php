<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Environment;

/**
 * The PhpEnvironment class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class PhpHelper
{
	/**
	 * isWeb
	 *
	 * @return  boolean
	 */
	public static function isWeb()
	{
		return in_array(
			PHP_SAPI,
			array(
				'apache',
				'cgi',
				'fast-cgi',
				'srv'
			)
		);
	}

	/**
	 * isCli
	 *
	 * @return  boolean
	 */
	public static function isCli()
	{
		return in_array(
			PHP_SAPI,
			array(
				'cli',
				'cli-server'
			)
		);
	}

	/**
	 * isHHVM
	 *
	 * @return  boolean
	 */
	public static function isHHVM()
	{
		return defined('HHVM_VERSION');
	}

	/**
	 * isEmbed
	 *
	 * @return  boolean
	 */
	public static function isEmbed()
	{
		return in_array(
			PHP_SAPI,
			array(
				'embed',
			)
		);
	}

	/**
	 * setStrict
	 *
	 * @return  void
	 */
	public static function setStrict()
	{
		error_reporting(32767);
	}

	/**
	 * setMuted
	 *
	 * @return  void
	 */
	public static function setMuted()
	{
		error_reporting(0);
	}

	/**
	 * supportPcntl
	 *
	 * @return  boolean
	 */
	public static function supportPcntl()
	{
		return extension_loaded('PCNTL');
	}

	/**
	 * supportCurl
	 *
	 * @return  boolean
	 */
	public static function supportCurl()
	{
		return function_exists('curl_init');
	}

	/**
	 * supportMcrypt
	 *
	 * @return  boolean
	 */
	public static function supportMcrypt()
	{
		return extension_loaded('mcrypt');
	}
}
