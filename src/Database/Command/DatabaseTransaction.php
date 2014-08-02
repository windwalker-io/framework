<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabaseAwareTrait;
use Windwalker\Database\Driver\DatabaseDriver;

/**
 * Class DatabaseTransaction
 *
 * @since 1.0
 */
abstract class DatabaseTransaction
{
	use DatabaseAwareTrait
	{
		DatabaseAwareTrait::__construct as doConstruct;
	}

	/**
	 * The depth of the current transaction.
	 *
	 * @var    integer
	 * @since  1.0
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

		$this->doConstruct($db);
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
	 * @return  DatabaseTransaction  Return self to support chaining.
	 */
	public function setNested($nested)
	{
		$this->nested = $nested;

		return $this;
	}
}

