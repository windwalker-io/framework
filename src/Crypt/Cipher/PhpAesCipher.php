<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Crypt\Cipher;

include_once __DIR__ . '/../lib/php-aes.php';

/**
 * The PhpAesCipher class.
 *
 * @since  {DEPLOY_VERSION}
 */
class PhpAesCipher implements CipherInterface
{
	const KEY_128BIT = 128;
	const KEY_192BIT = 192;
	const KEY_256BIT = 256;

	/**
	 * Property keyLength.
	 *
	 * @var  int
	 */
	protected $keyLength = self::KEY_128BIT;

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string $data The encrypted string to decrypt.
	 * @param   string $key  The private key.
	 * @param   string $iv   The public key.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since    2.0
	 */
	public function decrypt($data, $key = null, $iv = null)
	{
		$aes = new \AES($key);

		return $aes->decrypt($data);
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string $data The data string to encrypt.
	 * @param   string $key  The private key.
	 * @param   string $iv   The public key.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, $key = null, $iv = null)
	{
		$aes = new \AES($key);

		return $aes->encrypt($data);
	}
}
