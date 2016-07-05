<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The BlowfishChipher class.
 * 
 * @since  2.0
 */
class BlowfishCipher extends AbstractCipher
{
	/**
	 * @var    integer  The openssl cipher method.
	 * @see    http://php.net/manual/en/function.openssl-get-cipher-methods.php
	 * @since  3.0
	 */
	protected $method = 'bf';
}
