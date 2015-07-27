<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Crypt;

use Windwalker\Crypt\Cipher\CipherInterface;

/**
 * The Crypt class.
 * 
 * @since  2.0
 */
class Crypt
{
	/**
	 * Property cipher.
	 *
	 * @var CipherInterface
	 */
	protected $cipher;

	/**
	 * Property public.
	 *
	 * @var  string
	 */
	protected $iv;

	/**
	 * Property private.
	 *
	 * @var  null|string
	 */
	protected $key;

	/**
	 * Class init.
	 *
	 * @param CipherInterface $cipher
	 * @param string          $key
	 * @param string          $iv
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(CipherInterface $cipher, $key = null, $iv = null)
	{
		$this->cipher = $cipher;
		$this->iv = $iv;
		$this->key = $key;

		if (!is_string($this->key) && $this->key !== null)
		{
			throw new \InvalidArgumentException('Public key should be string');
		}
	}

	/**
	 * encrypt
	 *
	 * @param string $string
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  string
	 */
	public function encrypt($string, $key = null, $iv = null)
	{
		$key = $key ? : $this->getKey();
		$iv  = $iv  ? : $this->getIv();

		$encrypted = $this->cipher->encrypt($string, $key, $iv);

		return base64_encode($encrypted);
	}

	/**
	 * decrypt
	 *
	 * @param string $string
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  string
	 */
	public function decrypt($string, $key = null, $iv = null)
	{
		$string = base64_decode(str_replace(' ', '+', $string));

		$key = $key ? : $this->getKey();
		$iv  = $iv  ? : $this->getIv();

		return $this->cipher->decrypt($string, $key, $iv);
	}

	/**
	 * match
	 *
	 * @param string $string
	 * @param string $hash
	 * @param string $key
	 * @param string $iv
	 *
	 * @return  boolean
	 */
	public function verify($string, $hash, $key = null, $iv = null)
	{
		return ($string === $this->decrypt($hash, $key, $iv));
	}

	/**
	 * Method to get property Public
	 *
	 * @return  string
	 */
	public function getIV()
	{
		return $this->iv;
	}

	/**
	 * Method to set property public
	 *
	 * @param   string $iv
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setIV($iv)
	{
		$this->iv = $iv;

		return $this;
	}

	/**
	 * Method to get property Private
	 *
	 * @return  null|string
	 */
	public function getKey()
	{
		if (!$this->key)
		{
			$this->key = md5('To be, or not to be, that is the question.');
		}

		return $this->key;
	}

	/**
	 * Method to set property private
	 *
	 * @param   null|string $key
	 *
	 * @throws \InvalidArgumentException
	 * @return  static  Return self to support chaining.
	 */
	public function setKey($key)
	{
		$this->key = $key;

		if (!is_string($this->key) && $this->key !== null)
		{
			throw new \InvalidArgumentException('Key should be string');
		}

		return $this;
	}
}
