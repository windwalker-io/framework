<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt;

/**
 * The KeyInterface class.
 * 
 * @since  {DEPLOY_VERSION}
 */
interface KeyInterface
{
	/**
	 * Method to get property Type
	 *
	 * @return  int|null
	 */
	public function getType();

	/**
	 * Method to set property type
	 *
	 * @param   int|null $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setType($type);

	/**
	 * Method to get property Public
	 *
	 * @return  string
	 */
	public function getPublic();

	/**
	 * Method to set property public
	 *
	 * @param   string $public
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPublic($public);

	/**
	 * Method to get property Private
	 *
	 * @return  string
	 */
	public function getPrivate();

	/**
	 * Method to set property private
	 *
	 * @param   string $private
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPrivate($private);
}
 