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
	 * Property iv.
	 *
	 * @var string
	 */
	protected $iv;

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
	 * @param   string  $data     The encrypted string to decrypt.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @internal param \Windwalker\Crypt\KeyInterface $key The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since    1.0
	 */
	public function decrypt($data, $private = null, $public = null)
	{
		$data = base64_decode($data);

		if (!$public)
		{
			$ivSize = $this->getIVSize();

			$public = substr($data, 0, $ivSize);

			$data = substr($data, $ivSize);
		}

		// Decrypt the data.
		$decrypted = trim(mcrypt_decrypt($this->type, $private, $data, $this->mode, $public));

		return $decrypted;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data     The data string to encrypt.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function encrypt($data, $private = null, $public = null)
	{
		$public = $this->getIVKey();

		// Encrypt the data.
		$encrypted = mcrypt_encrypt($this->type, $private, $data, $this->mode, $public);

		return base64_encode($public . $encrypted);
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

			$this->iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
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
		return mcrypt_get_iv_size($this->type, $this->mode);
	}
}
 