<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Query\Query;

/**
 * Windwalker Query LimitableInterface.
 *
 * Adds bind/unbind methods as well as a getBounded() method
 * to retrieve the stored bounded variables on demand prior to
 * query execution.
 *
 * @since  {DEPLOY_VERSION}
 */
interface LimitableInterface
{
	/**
	 * Method to modify a query already in string format with the needed
	 * additions to make the query limited to a particular number of
	 * results, or start at a particular offset. This method is used
	 * automatically by the __toString() method if it detects that the
	 * query implements the LimitableInterface.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function processLimit($query, $limit, $offset = 0);

	/**
	 * Sets the offset and limit for the result set, if the database driver supports it.
	 *
	 * Usage:
	 * $query->setLimit(100, 0); (retrieve 100 rows, starting at first record)
	 * $query->setLimit(50, 50); (retrieve 50 rows, starting at 50th record)
	 *
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  LimitableInterface  Returns this object to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setLimit($limit = 0, $offset = 0);
}
