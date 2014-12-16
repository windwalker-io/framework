<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * APC session storage handler for PHP
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since  2.0
 */
class ApcHandler extends AbstractHandler
{
	/**
	 * Constructor
	 *
	 * @throws \RuntimeException
	 * @internal param array $options Optional parameters
	 *
	 * @since    2.0
	 */
	public function __construct($options)
	{
		if (!static::isSupported())
		{
			throw new \RuntimeException('APC Extension is not available', 500);
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
	public static function isSupported()
	{
		return extension_loaded('apc');
	}

	/**
	 * Read the data for a particular session identifier from the
	 * SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   2.0
	 */
	public function read($id)
	{
		return (string) apc_fetch($this->prefix . $id);
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
		return apc_store($this->prefix . $id, $session_data, ini_get("session.gc_maxlifetime"));
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
		return apc_delete($this->prefix . $id);
	}

	/**
	 * PHP >= 5.4.0<br/>
	 * Cleanup old sessions
	 *
	 * @link http://php.net/manual/en/sessionhandlerinterafce.gc.php
	 *
	 * @param int $maxlifetime <p>
	 *                         Sessions that have not updated for
	 *                         the last maxlifetime seconds will be removed.
	 *                         </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 */
	public function gc($maxlifetime)
	{
		return true;
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
}
