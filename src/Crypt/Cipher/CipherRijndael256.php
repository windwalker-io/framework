<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Rijndael256 class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class CipherRijndael256 extends McryptCipher
{
	/**
	 * @var    integer  The mcrypt cipher constant.
	 * @see    http://www.php.net/manual/en/mcrypt.ciphers.php
	 * @since  {DEPLOY_VERSION}
	 */
	protected $type = MCRYPT_RIJNDAEL_256;

	/**
	 * @var    integer  The mcrypt block cipher mode.
	 * @see    http://www.php.net/manual/en/mcrypt.constants.php
	 * @since  {DEPLOY_VERSION}
	 */
	protected $mode = MCRYPT_MODE_CBC;
}
