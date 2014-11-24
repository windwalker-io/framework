<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Driver\DatabaseDriver;

/**
 * Class DatabaseTransaction
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractTransaction
{
	/**
	 * Property driver.
	 *
	 * @var  \Windwalker\Database\Driver\DatabaseDriver
	 */
	protected $db;

	/**
	 * The depth of the current transaction.
	 *
	 * @var    integer
	 * @since  {DEPLOY_VERSION}
	 */
	protected $depth = 0;

	/**
	 * Property nested.
	 *
	 * @var  boolean
	 */
	protected $nested = true;

	/**
	 * Constructor.
	 *
	 * @param DatabaseDriver $db
	 * @param bool           $nested
	 */
	public function __construct(DatabaseDriver $db, $nested = true)
	{
		$this->nested = $nested;

		$this->db = $db;
	}

	/**
	 * start
	 *
	 * @return  static
	 */
	abstract public function start();

	/**
	 * commit
	 *
	 * @return  static
	 */
	abstract public function commit();

	/**
	 * rollback
	 *
	 * @return  static
	 */
	abstract public function rollback();

	/**
	 * getNested
	 *
	 * @return  boolean
	 */
	public function getNested()
	{
		return $this->nested;
	}

	/**
	 * setNested
	 *
	 * @param   boolean $nested
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setNested($nested)
	{
		$this->nested = $nested;

		return $this;
	}

	/**
	 * Method to get property Db
	 *
	 * @return  \Windwalker\Database\Driver\DatabaseDriver
	 */
	public function getDriver()
	{
		return $this->db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   \Windwalker\Database\Driver\DatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDriver($db)
	{
		$this->db = $db;

		return $this;
	}
}
