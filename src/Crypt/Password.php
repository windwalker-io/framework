<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Crypt;

/**
 * The SimplePassword class.
 * 
 * @since  2.0
 */
class Password
{
	const MD5 = 3;

	const BLOWFISH = 4;

	const SHA256 = 5;

	const SHA512 = 6;

	/**
	 * Property salt.
	 *
	 * @var  string
	 */
	protected $salt;

	/**
	 * Property cost.
	 *
	 * @var  integer
	 */
	protected $cost;

	/**
	 * Property type.
	 *
	 * @var  int
	 */
	protected $type;

	/**
	 * Constructor.
	 *
	 * @param int    $type
	 * @param int    $cost
	 * @param string $salt
	 */
	public function __construct($type = self::BLOWFISH, $cost = 10, $salt = null)
	{
		$this->setSalt($salt);
		$this->setCost($cost);
		$this->type = $type;
	}

	/**
	 * create
	 *
	 * @param string $password
	 *
	 * @return  string
	 */
	public function create($password)
	{
		$salt = $this->salt ? : str_replace('+', '.', base64_encode(CryptHelper::genRandomBytes(64)));

		switch ($this->type)
		{
			case static::MD5:
				$salt = '$1$' . $salt . '$';
				break;

			case static::SHA256:
				$cost = CryptHelper::limitInteger($this->cost, 1000);

				$salt = '$5$rounds=' . $cost . '$' . $salt . '$';
				break;

			case static::SHA512:
				$cost = CryptHelper::limitInteger($this->cost, 1000);

				$salt = '$6$rounds=' . $cost . '$' . $salt . '$';
				break;

			default:
			case static::BLOWFISH:
				$prefix = (version_compare(PHP_VERSION, '5.3.7') >= 0) ? '$2y$' : '$2a$';

				$salt = CryptHelper::repeatToLength($salt, 21);

				$salt = $prefix . CryptHelper::limitInteger($this->cost, 4, 31) . '$' . $salt . '$';
				break;
		}

		return crypt($password, $salt);
	}

	/**
	 * Verify the password.
	 *
	 * @param   string   $password  The password plain text.
	 * @param   string   $hash      The hashed password.
	 *
	 * @return  boolean  Verify success or not.
	 *
	 * @see  https://github.com/ircmaxell/password_compat/blob/92951ae05e988803fdc1cd49f7e4cd29ca7b75e9/lib/password.php#L230-L247
	 */
	public function verify($password, $hash)
	{
		if (!function_exists('crypt'))
		{
			trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);

			return false;
		}

		// Calculate the user-provided hash, using the salt stored with the known hash
		$ret = crypt($password, $hash);

		if (!is_string($ret) || CryptHelper::getLength($ret) != CryptHelper::getLength($hash) || CryptHelper::getLength($ret) <= 13)
		{
			return false;
		}

		$status = 0;
		$len = CryptHelper::getLength($ret);

		for ($i = 0; $i < $len; ++$i)
		{
			$status |= (ord($ret[$i]) ^ ord($hash[$i]));
		}

		return $status === 0;
	}

	/**
	 * Generate a random password.
	 *
	 * This is a fork of Joomla JUserHelper::genRandomPassword()
	 *
	 * @param   integer  $length  Length of the password to generate
	 *
	 * @return  string  Random Password
	 *
	 * @see     https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/user/helper.php#L642
	 * @since   2.0.9
	 */
	public static function genRandomPassword($length = 8)
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$base = strlen($salt);
		$password = '';

		/*
		 * Start with a cryptographic strength random string, then convert it to
		 * a string with the numeric base of the salt.
		 * Shift the base conversion on each character so the character
		 * distribution is even, and randomize the start shift so it's not
		 * predictable.
		 */
		$random = CryptHelper::genRandomBytes($length + 1);
		$shift = ord($random[0]);

		for ($i = 1; $i <= $length; ++$i)
		{
			$password .= $salt[($shift + ord($random[$i])) % $base];

			$shift += ord($random[$i]);
		}

		return $password;
	}

	/**
	 * Method to get property Salt
	 *
	 * @return  string
	 */
	public function getSalt()
	{
		return $this->salt;
	}

	/**
	 * Method to set property salt
	 *
	 * @param   string $salt
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSalt($salt)
	{
		$this->salt = $salt;

		return $this;
	}

	/**
	 * Method to get property Cost
	 *
	 * @return  int
	 */
	public function getCost()
	{
		return $this->cost;
	}

	/**
	 * Method to set property cost
	 *
	 * @param   int $cost
	 *
	 * @throws \InvalidArgumentException
	 * @return  static  Return self to support chaining.
	 */
	public function setCost($cost)
	{
		$this->cost = $cost;

		return $this;
	}

	/**
	 * Method to get property Type
	 *
	 * @return  int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to set property type
	 *
	 * @param   int $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}
}
