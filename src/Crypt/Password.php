<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt;

/**
 * The SimplePassword class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Password
{
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
	 * Constructor.
	 *
	 * @param int    $cost
	 * @param string $salt
	 */
	public function __construct($cost = 10, $salt = null)
	{
		$this->setSalt($salt);
		$this->setCost($cost);
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
		$salt = $this->salt ? : CryptHelper::genRandomBytes(16);

		$salt64 = substr(str_replace('+', '.', base64_encode($salt)), 0, 22);

		if (version_compare(PHP_VERSION, '5.3.7') >= 0)
		{
			$prefix = '$2y$';
		}
		else
		{
			$prefix = '$2a$';
		}

		return crypt($password, $prefix . $this->cost . '$' . $salt64);
	}

	/**
	 * verify
	 *
	 * @param string $password
	 * @param string $hash
	 *
	 * @return  boolean
	 */
	public function verify($password, $hash)
	{
		$prefix = substr($hash, 0, 4);

		if ($prefix != '$2y$' && $prefix != '$2a$')
		{
			return false;
		}

		return ($hash === crypt($password, $hash));
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
		if (!empty($cost))
		{
			$cost = (int) $cost;

			if ($cost < 4 || $cost > 31)
			{
				throw new \InvalidArgumentException('The cost must be in range 04-31');
			}
		}

		$this->cost = $cost;

		return $this;
	}
}
