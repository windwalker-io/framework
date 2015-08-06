<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Authentication;

use Windwalker\Authentication\Method\MethodInterface;

/**
 * The Authentication class.
 * 
 * @since  2.0
 */
class Authentication
{
	const SUCCESS = 1;

	const INVALID_CREDENTIAL = 2;

	const EMPTY_CREDENTIAL = 3;

	const USER_NOT_FOUND = 4;

	/**
	 * Property results.
	 *
	 * @var  integer[]
	 */
	protected $results = array();

	/**
	 * Property methods.
	 *
	 * @var  MethodInterface[]
	 */
	protected $methods = array();

	/**
	 * Property credential.
	 *
	 * @var Credential
	 */
	protected $credential;

	/**
	 * authenticate
	 *
	 * @param Credential $credential
	 *
	 * @return  bool|Credential
	 */
	public function authenticate(Credential $credential)
	{
		$this->results = array();

		foreach ($this->methods AS $name => $method)
		{
			$result = $method->authenticate($credential);
			$status = $method->getStatus();

			$this->results[$name] = $status;
			$this->credential = $credential;

			if ($result === true && $status === static::SUCCESS)
			{
				$credential['_authenticated_method'] = $name;

				return true;
			}
		}

		return false;
	}

	/**
	 * addMethod
	 *
	 * @param string          $name
	 * @param MethodInterface $method
	 *
	 * @return  static
	 */
	public function addMethod($name, MethodInterface $method)
	{
		$this->methods[$name] = $method;

		return $this;
	}

	/**
	 * getMethod
	 *
	 * @param string $name
	 *
	 * @return  MethodInterface
	 */
	public function getMethod($name)
	{
		if (isset($this->methods[$name]))
		{
			return $this->methods[$name];
		}

		return null;
	}

	/**
	 * removeMethod
	 *
	 * @param string $name
	 *
	 * @return  $this
	 */
	public function removeMethod($name)
	{
		 if (isset($this->methods[$name]))
		 {
			 unset($this->methods[$name]);
		 }

		return $this;
	}

	/**
	 * Method to get property Results
	 *
	 * @return  integer[]
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 * Method to get property Credential
	 *
	 * @return  Credential
	 */
	public function getCredential()
	{
		return $this->credential;
	}
}
