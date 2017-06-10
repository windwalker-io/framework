<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 Asikart.com.
 * @license    __LICENSE__
 */

namespace Windwalker\Crypt;

/**
 * Interface HashInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface HasherInterface
{
	/**
	 * create
	 *
	 * @param string $text
	 *
	 * @return  string
	 */
	public function create($text);

	/**
	 * Verify the password.
	 *
	 * @param   string   $text  The plain text.
	 * @param   string   $hash  The hashed text.
	 *
	 * @return  boolean  Verify success or not.
	 */
	public function verify($text, $hash);
}
