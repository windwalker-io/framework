<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use Windwalker\Registry\Registry;

/**
 * Class AbstractDatabaseModel
 *
 * @since 1.0
 */
abstract class AbstractDatabaseModel extends AbstractModel
{
	/**
	 * The database driver.
	 *
	 * @var  object
	 */
	protected $db;

	/**
	 * Instantiate the model.
	 *
	 * @param   object    $db     The database adapter.
	 * @param   Registry  $state  The model state.
	 */
	public function __construct($db = null, Registry $state = null)
	{
		$this->db = $db;

		parent::__construct($state);
	}

	/**
	 * Get the database driver.
	 *
	 * @return  object  The database driver.
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Set the database driver.
	 *
	 * @param   object  $db  The database driver.
	 *
	 * @return  void
	 */
	public function setDb($db)
	{
		$this->db = $db;
	}
}
 