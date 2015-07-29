<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Model;

/**
 * The DatabaseModelInterface class.
 * 
 * @since  2.0
 */
interface DatabaseModelInterface extends ModelInterface
{
	/**
	 * Get the database driver.
	 *
	 * @return  object  The database driver.
	 */
	public function getDb();

	/**
	 * Set the database driver.
	 *
	 * @param   object  $db  The database driver.
	 *
	 * @return  void
	 */
	public function setDb($db);
}
