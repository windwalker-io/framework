<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
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

//	/**
//	 * getKeyChars
//	 *
//	 * @return  string
//	 */
//	public function getKeyChars()
//	{
//		switch ($this->keyLength)
//		{
//			case static::KEY_128BIT:
//				return 'abcdefgh01234567';
//			case static::KEY_192BIT:
//				return 'abcdefghijkl012345678901';
//			case static::KEY_256BIT:
//				return 'abcdefghijuklmno0123456789012345';
//			default:
//				throw new \InvalidArgumentException('Please use 256bit, 192bit or 128bit key length.');
//		}
//	}
//
//	/**
//	 * Method to get property KeyLength
//	 *
//	 * @return  int
//	 */
//	public function getKeyLength()
//	{
//		return $this->keyLength;
//	}
//
//	/**
//	 * Method to set property keyLength
//	 *
//	 * @param   int $keyLength
//	 *
//	 * @return  static  Return self to support chaining.
//	 */
//	public function setKeyLength($keyLength)
//	{
//		$this->keyLength = $keyLength;
//
//		return $this;
//	}
}
