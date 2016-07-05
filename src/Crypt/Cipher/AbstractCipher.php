<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Cipher;

use Windwalker\Crypt\CryptHelper;

if (defined('OPENSSL_RAW_DATA'))
{
	define('OPENSSL_RAW_DATA', 1);
}

/**
 * The Openssl Cipher class.
 * 
 * @since  2.0
 */
abstract class AbstractCipher implements CipherInterface
{
	/**
	 * Property type.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Property mode.
	 *
	 * @var int
	 */
	protected $mode = 'cbc';

	/**
	 * Property iv.
	 *
	 * @var string
	 */
	protected $iv;

	/**
	 * Constructor.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function __construct()
	{
		if (!is_callable('openssl_encrypt'))
		{
			throw new \RuntimeException('The openssl extension is not available.');
		}
	}

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
	public function encrypt($data, $key = null, $iv = null)
	{
		$iv = $iv ? : $this->getIVKey();

		$key = CryptHelper::repeatToLength($key, 24, true);

		// Encrypt the data.
		$encrypted = openssl_encrypt($data, $this->getMethod(), $key, OPENSSL_RAW_DATA, $iv);

		return $iv . $encrypted;
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   string  $key   The private key.
	 * @param   string  $iv    The public key.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since    2.0
	 */
	public function decrypt($data, $key = null, $iv = null)
	{
		if (!$iv)
		{
			$ivSize = $this->getIVSize();

			$iv = substr($data, 0, $ivSize);

			$data = substr($data, $ivSize);
		}
		else
		{
			$ivSize = $this->getIVSize();

			if (substr($data, 0, $ivSize) === $iv)
			{
				$data = substr($data, $ivSize);
			}
		}

		$key = CryptHelper::repeatToLength($key, 24, true);

		// Decrypt the data.
		$decrypted = trim(openssl_decrypt($data, $this->getMethod(), $key, OPENSSL_RAW_DATA, $iv));

		return $decrypted;
	}

	/**
	 * getIVKey
	 *
	 * @return  string
	 */
	public function getIVKey()
	{
		if (!$this->iv)
		{
			$ivSize = $this->getIVSize();

			$this->iv = CryptHelper::genRandomBytes($ivSize);
		}

		return $this->iv;
	}

	/**
	 * getIVSize
	 *
	 * @return  integer
	 */
	public function getIVSize()
	{
		return openssl_cipher_iv_length($this->getMethod());
	}

	/**
	 * Method to get property Type
	 *
	 * @return  string
	 */
	public function getMethod()
	{
		if (!$this->mode)
		{
			return $this->method;
		}

		return $this->method . '-' . $this->mode;
	}
}
