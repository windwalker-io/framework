<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Cipher3DES class.
 * 
 * @since  2.0
 */
class Des3Cipher extends AbstractCipher
{
	/**
	 * @var    integer  The openssl cipher method.
	 * @see    http://php.net/manual/en/function.openssl-get-cipher-methods.php
	 * @since  3.0
	 */
	protected $method = 'des-ede3';

	/**
	 * Property mode.
	 *
	 * @var  string
	 */
	protected $mode = 'cbc';
}
