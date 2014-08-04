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
	 * Property key.
	 *
	 * @var  KeyInterface
	 */
	protected $key;

	/**
	 * Class init.
	 *
	 * @param CipherInterface $cipher
	 * @param KeyInterface    $key
	 */
	public function __construct(CipherInterface $cipher, KeyInterface $key)
	{
		$this->cipher = $cipher;
		$this->key    = $key;
	}

	/**
	 * encrypt
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public function encrypt($string)
	{
		return $this->cipher->encrypt($string, $this->key);
	}

	/**
	 * decrypt
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public function decrypt($string)
	{
		return $this->cipher->decrypt($string, $this->key);
	}
}
 