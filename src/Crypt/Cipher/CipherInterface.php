<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * Interface CipherInterface
 *
 * @since  2.0
 */
interface CipherInterface
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   string  $key   The private key.
	 * @param   string  $iv    The public key.
	 *
	 * @internal param \Windwalker\Crypt\KeyInterface $key The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since    2.0
	 */
	public function decrypt($data, $key = null, $iv = null);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 * @param   string  $key   The private key.
	 * @param   string  $iv    The public key.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, $key = null, $iv = null);
}
 