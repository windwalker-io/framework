<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt;

/**
 * The Key class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Key implements KeyInterface
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = null;

	/**
	 * Property public.
	 *
	 * @var  string
	 */
	protected $public = null;

	/**
	 * Property private.
	 *
	 * @var  string
	 */
	protected $private = null;

	/**
	 * Class init.
	 *
	 * @param string  $type
	 * @param string  $private
	 * @param string  $public
	 */
	public function __construct($type, $private, $public)
	{
		$this->type    = $type;
		$this->private = $private;
		$this->public  = $public;
	}

	/**
	 * Method to get property Type
	 *
	 * @return  int|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to set property type
	 *
	 * @param   int|null $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
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
	 * @return  string
	 */
	public function getPrivate()
	{
		return $this->private;
	}

	/**
	 * Method to set property private
	 *
	 * @param   string $private
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPrivate($private)
	{
		$this->private = $private;

		return $this;
	}
}
 