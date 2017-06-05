<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The SodiumCipher class.
 *
 * @since  3.2
 */
class SodiumCipher extends AbstractCipher
{
	/**
	 * Property key.
	 *
	 * @var  string
	 */
	protected $key;

	/**
	 * Property iv.
	 *
	 * @var  string
	 */
	protected $nonce;

	/**
	 * Constructor.
	 *
	 * @param string $key
	 *
	 * @param array  $options
	 *
	 * @since  3.2
	 *
	 */
	public function __construct($key = null, array $options = [])
	{
		if (!function_exists('Sodium\crypto_secretbox_open'))
		{
			throw new \DomainException('Please install ext-libsodium first.');
		}

		parent::__construct($key ? : static::genRandomBytes(static::getKeySize()), $options);
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string $data  The encrypted string to decrypt.
	 * @param   string $key   The private key.
	 * @param   string $nonce The public key.
	 *
	 * @return  string  The decrypted data string.
	 * @throws \RuntimeException
	 *
	 * @since    3.2
	 */
	public function decrypt($data, $key = null, $nonce = null)
	{
		$plain = parent::decrypt($data, $key, $nonce);

		$key = (string) $key;
		$nonce = (string) $nonce;

		\Sodium\memzero($data);
		\Sodium\memzero($key);
		\Sodium\memzero($nonce);

		return $plain;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string $data  The data string to encrypt.
	 * @param   string $key   The private key.
	 * @param   string $nonce The public key.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   3.2
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, $key = null, $nonce = null)
	{
		$encrypted = parent::encrypt($data, $key, $nonce);

		$key = (string) $key;
		$nonce = (string) $nonce;

		\Sodium\memzero($data);
		\Sodium\memzero($key);
		\Sodium\memzero($nonce);

		return $encrypted;
	}

	/**
	 * getIVKey
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	public function getNonce()
	{
		if (!$this->nonce)
		{
			$this->nonce = \Sodium\randombytes_buf(static::getNonceSize());
		}

		return $this->nonce;
	}

	/**
	 * getNonceSize
	 *
	 * @return  int
	 */
	public static function getNonceSize()
	{
		return \Sodium\CRYPTO_SECRETBOX_NONCEBYTES;
	}

	/**
	 * getKeySize
	 *
	 * @return  int
	 */
	public static function getKeySize()
	{
		return \Sodium\CRYPTO_SECRETBOX_KEYBYTES;
	}

	/**
	 * genRandomBytes
	 *
	 * @param int $size
	 *
	 * @return  string
	 */
	public static function genRandomBytes($size = \Sodium\CRYPTO_SECRETBOX_KEYBYTES)
	{
		return \Sodium\randombytes_buf($size);
	}

	/**
	 * doEncrypt
	 *
	 * @param   string $data The data string to encrypt.
	 * @param   string $key  The private key.
	 * @param   string $iv   The public key.
	 *
	 * @return  string
	 */
	protected function doEncrypt($data, $key, $iv)
	{
		$encrypted = \Sodium\crypto_secretbox($data, $iv, $key);

		\Sodium\memzero($data);
		\Sodium\memzero($key);
		\Sodium\memzero($iv);

		return $encrypted;
	}

	/**
	 * doDecrypt
	 *
	 * @param   string $data The encrypted string to decrypt.
	 * @param   string $key  The private key.
	 * @param   string $iv   The public key.
	 *
	 * @return  string
	 */
	protected function doDecrypt($data, $key, $iv)
	{
		$plain = \Sodium\crypto_secretbox_open($data, $iv, $key);

		\Sodium\memzero($data);
		\Sodium\memzero($key);
		\Sodium\memzero($iv);

		return $plain;
	}

	/**
	 * randomPseudoBytes
	 *
	 * @param int $size
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	protected function randomPseudoBytes($size = \Sodium\CRYPTO_SECRETBOX_KEYBYTES)
	{
		return static::genRandomBytes($size);
	}

	/**
	 * getIVSize
	 *
	 * @return  integer
	 */
	public function getIVSize()
	{
		return static::getNonceSize();
	}
}
