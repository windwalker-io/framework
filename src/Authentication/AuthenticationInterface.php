<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authentication;

use Windwalker\Authentication\Method\MethodInterface;

/**
 * Interface AuthenticationInterface
 *
 * @since  {DEPLOY_VERSION}
 */
interface AuthenticationInterface
{
	/**
	 * authenticate
	 *
	 * @param Credential $credential
	 *
	 * @return  bool|Credential
	 */
	public function authenticate(Credential $credential);

	/**
	 * addMethod
	 *
	 * @param string          $name
	 * @param MethodInterface $method
	 *
	 * @return  static
	 */
	public function addMethod($name, MethodInterface $method);

	/**
	 * Method to get property Results
	 *
	 * @return  integer[]
	 */
	public function getResults();

	/**
	 * Method to get property Credential
	 *
	 * @return  Credential
	 */
	public function getCredential();
}
