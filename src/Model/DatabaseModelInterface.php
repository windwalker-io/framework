<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Model;

/**
 * The DatabaseModelInterface class.
 * 
 * @since  {DEPLOY_VERSION}
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
 