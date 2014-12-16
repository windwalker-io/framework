<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\AbstractTransaction;

/**
 * Class PdoTransaction
 *
 * @since 2.0
 */
class PdoTransaction extends AbstractTransaction
{
	/**
	 * start
	 *
	 * @return  static
	 */
	public function start()
	{
		if (!$this->nested || !$this->depth)
		{
			$this->db->connect()->getConnection()->beginTransaction();
		}

		$this->depth++;

		return $this;
	}

	/**
	 * commit
	 *
	 * @return  static
	 */
	public function commit()
	{
		if (!$this->nested || $this->depth == 1)
		{
			$this->db->connect()->getConnection()->commit();
		}

		$this->depth--;

		return $this;
	}

	/**
	 * rollback
	 *
	 * @return  static
	 */
	public function rollback()
	{
		if (!$this->nested || $this->depth == 1)
		{
			$this->db->connect()->getConnection()->rollBack();
		}

		$this->depth--;

		return $this;
	}
}

