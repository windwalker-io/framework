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
 * The McryptCipher class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class McryptCipher implements CipherInterface
{
	/**
	 * Property type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Property mode.
	 *
	 * @var string
	 */
	protected $mode;

	/**
	 * Constructor.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct()
	{
		if (!is_callable('mcrypt_encrypt'))
		{
			throw new \RuntimeException('The mcrypt extension is not available.');
		}
	}

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
	public function decrypt($data, KeyInterface $key)
	{
		// Decrypt the data.
		$decrypted = trim(mcrypt_decrypt($this->type, $key->getPrivate(), $data, $this->mode, $key->getPublic()));

		return $decrypted;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string        $data  The data string to encrypt.
	 * @param   KeyInterface  $key   The key object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, KeyInterface $key)
	{
		// Encrypt the data.
		$encrypted = mcrypt_encrypt($this->type, $key->getPrivate(), $data, $this->mode, $key->getPublic());

		return $encrypted;
	}
}
 