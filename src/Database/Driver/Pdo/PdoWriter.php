<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Database\Command\DatabaseWriter;

/**
 * Class PdoWriter
 *
 * @since {DEPLOY_VERSION}
 */
class PdoWriter extends DatabaseWriter
{
	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function insertId()
	{
		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->db->getConnection()->lastInsertId();
	}
}

