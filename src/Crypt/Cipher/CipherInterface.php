<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt\Cipher;

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
	 * @param   string  $data     The encrypted string to decrypt.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @internal param \Windwalker\Crypt\KeyInterface $key The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since    {DEPLOY_VERSION}
	 */
	public function decrypt($data, $private = null, $public = null);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data     The data string to encrypt.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, $private = null, $public = null);
}
 