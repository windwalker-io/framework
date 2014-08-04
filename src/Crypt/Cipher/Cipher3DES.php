<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Cipher3DES class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Cipher3DES extends McryptCipher
{
	/**
	 * @var    integer  The mcrypt cipher constant.
	 * @see    http://www.php.net/manual/en/mcrypt.ciphers.php
	 * @since  1.0
	 */
	protected $type = MCRYPT_3DES;

	/**
	 * @var    integer  The mcrypt block cipher mode.
	 * @see    http://www.php.net/manual/en/mcrypt.constants.php
	 * @since  1.0
	 */
	protected $mode = MCRYPT_MODE_CBC;
}
 