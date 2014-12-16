<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Class XcacheHandler
 *
 * @since 2.0
 */
class XcacheHandler extends AbstractHandler
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   2.0
	 * @throws  \RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!static::isSupported())
		{
			throw new \RuntimeException('XCache Extension is not available', 500);
		}

		parent::__construct($options);
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	static public function isSupported()
	{
		return (extension_loaded('xcache'));
	}

	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   2.0
	 */
	public function read($id)
	{
		// Check if id exists
		if (!xcache_isset($this->prefix . $id))
		{
			return false;
		}

		return (string) xcache_get($this->prefix . $id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id            The session identifier.
	 * @param   string  $session_data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	public function write($id, $session_data)
	{
		return xcache_set($this->prefix . $id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.0
	 */
	public function destroy($id)
	{
		$sess_id = $this->prefix . $id;

		if (!xcache_isset($sess_id))
		{
			return true;
		}

		return xcache_unset($sess_id);
	}

	/**
	 * Re-initializes existing session, or creates a new one.
	 *
	 * @see http://php.net/sessionhandlerinterface.open
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
	 * @see http://php.net/sessionhandlerinterface.close
	 *
	 * @return bool true on success, false on failure
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Cleans up expired sessions (garbage collection).
	 *
	 * @see http://php.net/sessionhandlerinterface.gc
	 *
	 * @param string|int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed
	 *
	 * @return bool true on success, false on failure
	 */
	public function gc($maxlifetime)
	{
		return true;
	}
}

