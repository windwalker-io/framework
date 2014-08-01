<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\DatabaseTransaction;

/**
 * Class PdoTransaction
 *
 * @since 1.0
 */
class PdoTransaction extends DatabaseTransaction
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

