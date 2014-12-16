<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Database;

/**
 * Class AbstractDatabaseAdapter
 *
 * @since 2.0
 */
abstract class AbstractDatabaseAdapter
{
	/**
	 * Property db.
	 *
	 * @var  object
	 */
	protected $db = null;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'table'    => 'windwalker_sessions',
		'id_col'   => 'id',
		'data_col' => 'data',
		'time_col' => 'time'
	);

	/**
	 * Class init.
	 *
	 * @param object $db
	 * @param array  $options
	 */
	public function __construct($db, $options = array())
	{
		$this->db = $db;

		$this->options = array_merge($this->options, $options);
	}

	/**
	 * read
	 *
	 * @param string|int $id
	 *
	 * @return  string
	 */
	abstract public function read($id);

	/**
	 * write
	 *
	 * @param string|int $id
	 * @param string     $data
	 *
	 * @return  boolean
	 */
	abstract public function write($id, $data);

	/**
	 * destroy
	 *
	 * @param string|int $id
	 *
	 * @return  boolean
	 */
	abstract public function destroy($id);

	/**
	 * gc
	 *
	 * @param string $past
	 *
	 * @return  bool
	 */
	abstract public function gc($past);

	/**
	 * getDb
	 *
	 * @return  object
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * setDb
	 *
	 * @param   object $db
	 *
	 * @return  AbstractDatabaseAdapter  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}
}

