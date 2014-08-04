<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt;

use Windwalker\Crypt\Cipher\CipherInterface;

/**
 * The Crypt class.
 * 
 * @since  {DEPLOY_VERSION}
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
	protected $public;

	/**
	 * Property private.
	 *
	 * @var  null|string
	 */
	protected $private;

	/**
	 * Class init.
	 *
	 * @param CipherInterface $cipher
	 * @param string          $private
	 * @param string          $public
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(CipherInterface $cipher, $private = null, $public = null)
	{
		$this->cipher = $cipher;
		$this->public = $public;
		$this->private  = $private;

		if (!is_string($this->private))
		{
			throw new \InvalidArgumentException('Public key should be string');
		}
	}

	/**
	 * encrypt
	 *
	 * @param string $string
	 * @param string $private
	 * @param string $public
	 *
	 * @return  string
	 */
	public function encrypt($string, $private = null, $public = null)
	{
		$private = $private ? : $this->getPrivate();
		$public  = $public  ? : $this->getPublic();

		$encrypted = $this->cipher->encrypt($string, $private, $public);

		return base64_encode($encrypted);
	}

	/**
	 * decrypt
	 *
	 * @param string $string
	 * @param string $private
	 * @param string $public
	 *
	 * @return  string
	 */
	public function decrypt($string, $private = null, $public = null)
	{
		$string = base64_decode(str_replace(' ', '+', $string));

		$private = $private ? : $this->getPrivate();
		$public  = $public  ? : $this->getPublic();

		return $this->cipher->decrypt($string, $private, $public);
	}

	/**
	 * match
	 *
	 * @param string $hash
	 * @param string $string
	 * @param string $private
	 * @param string $public
	 *
	 * @return  boolean
	 */
	public function verify($hash, $string, $private = null, $public = null)
	{
		return ($string === $this->decrypt($hash, $private, $public));
	}

	/**
	 * Method to get property Public
	 *
	 * @return  string
	 */
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * Method to set property public
	 *
	 * @param   string $public
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPublic($public)
	{
		$this->public = $public;

		return $this;
	}

	/**
	 * Method to get property Private
	 *
	 * @return  null|string
	 */
	public function getPrivate()
	{
		if (!$this->private)
		{
			$this->private = md5('To be, or not to be, that is the question.');
		}

		return $this->private;
	}

	/**
	 * Method to set property private
	 *
	 * @param   null|string $private
	 *
	 * @throws \InvalidArgumentException
	 * @return  static  Return self to support chaining.
	 */
	public function setPrivate($private)
	{
		$this->private = $private;

		if (!is_string($this->private))
		{
			throw new \InvalidArgumentException('Public key should be string');
		}

		return $this;
	}
}
 