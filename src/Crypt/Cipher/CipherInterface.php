<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt\Cipher;

use Windwalker\Crypt\KeyInterface;

/**
 * Interface CipherInterface
 *
 * @since  {DEPLOY_VERSION}
 */
interface CipherInterface
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string        $data  The encrypted string to decrypt.
	 * @param   KeyInterface  $key   The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function decrypt($data, KeyInterface $key);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string        $data  The data string to encrypt.
	 * @param   KeyInterface  $key   The key object to use for decryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, KeyInterface $key);
}
 