<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Handler;

use Windwalker\Session\Database\AbstractDatabaseAdapter;

/**
 * Database session storage handler for PHP
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since  2.0
 */
class DatabaseHandler extends AbstractHandler
{
	/**
	 * The DatabaseAdapter to use when querying.
	 *
	 * @var AbstractDatabaseAdapter
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param  AbstractDatabaseAdapter $db
	 *
	 * @throws \RuntimeException
	 * @since   2.0
	 */
	public function __construct(AbstractDatabaseAdapter $db)
	{
		$this->db = $db;
	}

	/**
	 * Re-initializes existing session, or creates a new one.
	 *
	 * @param string $savePath    Save path
	 * @param string $sessionName Session name, see http://php.net/function.session-name.php
	 *
	 * @return bool true on success, false on failure
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * Closes the current session.
	 *
	 * @return bool true on success, false on failure
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string $id The session identifier.
	 *
	 * @throws \Exception
	 * @return  string  The session data.
	 *
	 * @since   2.0
	 */
	public function read($id)
	{
		try
		{
			return $this->db->read($id);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string $id   The session identifier.
	 * @param   string $data The session data.
	 *
	 * @throws  \Exception
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	public function write($id, $data)
	{
		try
		{
			return $this->db->write($id, $data);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string $id The session identifier.
	 *
	 * @throws \Exception
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	public function destroy($id)
	{
		try
		{
			return $this->db->destroy($id);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param   integer $lifetime The maximum age of a session.
	 *
	 * @throws  \Exception
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	public function gc($lifetime = 1440)
	{
		// Determine the timestamp threshold with which to purge old sessions.
		$past = time() - $lifetime;

		try
		{
			return $this->db->gc($past);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * isSupported
	 *
	 * @return  boolean
	 */
	public static function isSupported()
	{
		return true;
	}
}
