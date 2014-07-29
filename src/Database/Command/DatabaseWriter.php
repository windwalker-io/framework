<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\DatabseAwareTrait;
use Windwalker\Query\QueryHelper;

/**
 * Class DatabaseWriter
 *
 * @since 1.0
 */
class DatabaseWriter
{
	use DatabseAwareTrait;

	/**
	 * Batch update some data.
	 *
	 * @param string $table      Table name.
	 * @param string $data       Data you want to update.
	 * @param mixed  $conditions Where conditions, you can use array or Compare object.
	 *                           Example:
	 *                           - `array('id' => 5)` => id = 5
	 *                           - `new GteCompare('id', 20)` => 'id >= 20'
	 *                           - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
	 *
	 * @return  boolean True if update success.
	 */
	public function updateBatch($table, $data, $conditions = array())
	{
		$query = $this->db->getQuery(true);

		// Build conditions
		$query = QueryHelper::buildWheres($query, $conditions);

		// Build update values.
		$fields = array_keys($this->db->getTable()->getColumns($table));

		$hasField = false;

		foreach ((array) $data as $field => $value)
		{
			if (!in_array($field, $fields))
			{
				continue;
			}

			$query->set($query->format('%n = %q', $field, $value));

			$hasField = true;
		}

		if (!$hasField)
		{
			return false;
		}

		$query->update($table);

		return $this->db->setQuery($query)->execute();
	}
}
 