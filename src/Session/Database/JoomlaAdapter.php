<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Database;

use Joomla\Database\DatabaseDriver;

/**
 * Class DatabaseAdapter
 *
 * @since 2.0
 */
class JoomlaAdapter extends AbstractDatabaseAdapter
{
	/**
	 * Property db.
	 *
	 * @var  \Joomla\Database\DatabaseDriver
	 */
	protected $db = null;

	/**
	 * Class init.
	 *
	 * @param DatabaseDriver $db
	 * @param array          $options
	 */
	public function __construct(DatabaseDriver $db, $options = array())
	{
		parent::__construct($db, $options);
	}

	/**
	 * read
	 *
	 * @param string|int $id
	 *
	 * @return  string
	 */
	public function read($id)
	{
		// Get the session data from the database table.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName($this->options['data_col']))
			->from($this->db->quoteName($this->options['table']))
			->where($this->db->quoteName($this->options['id_col']) . ' = ' . $this->db->quote($id));

		$this->db->setQuery($query);

		return (string) $this->db->loadResult();
	}

	/**
	 * write
	 *
	 * @param string|int $id
	 * @param string     $data
	 *
	 * @return  boolean
	 */
	public function write($id, $data)
	{
		$data = array(
			$this->options['data_col'] => $data,
			$this->options['time_col'] => (int) time(),
			$this->options['id_col'] => $id,
		);

		$data = (object) $data;

		$this->db->updateObject($this->options['table'], $data, $this->options['id_col']);

		if ($this->db->getAffectedRows())
		{
			return true;
		}

		$this->db->insertObject($this->options['table'], $data, $this->options['id_col']);

		return true;
	}

	/**
	 * destroy
	 *
	 * @param string|int $id
	 *
	 * @return  boolean
	 */
	public function destroy($id)
	{
		$query = $this->db->getQuery(true);
		$query->delete($this->db->quoteName($this->options['table']))
			->where($this->db->quoteName($this->options['id_col']) . ' = ' . $this->db->quote($id));

		// Remove a session from the database.
		$this->db->setQuery($query);

		return (boolean) $this->db->execute();
	}

	/**
	 * gc
	 *
	 * @param string $past
	 *
	 * @return  bool
	 */
	public function gc($past)
	{
		$query = $this->db->getQuery(true);
		$query->delete($this->db->quoteName($this->options['table']))
			->where($this->db->quoteName($this->options['time_col']) . ' < ' . $this->db->quote((int) $past));

		// Remove expired sessions from the database.
		$this->db->setQuery($query);

		return (boolean) $this->db->execute();
	}
}
