<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/SessionHandlerInterface.php';

/**
 * The SessionHandler class.
 *
 * @since  {DEPLOY_VERSION}
 */
class SessionHandler implements SessionHandlerInterface
{
	/**
	 * Close the session
	 *
	 * @return bool The return value (usually TRUE on success, FALSE on failure).
	 *              Note this value is returned internally to PHP for processing.
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Destroy a session
	 *
	 * @param   int   $id  The session ID being destroyed.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function destroy($id)
	{
		return true;
	}

	/**
	 * Cleanup old sessions
	 *
	 * @param   int  $maxlifetime  Sessions that have not updated for
	 *                             the last maxlifetime seconds will be removed.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function gc($maxlifetime)
	{
		return true;
	}

	/**
	 * Initialize session
	 *
	 * @param   string  $savePath  The path where to store/retrieve the session.
	 * @param   string  $id        The session id.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function open($savePath, $id)
	{
		return true;
	}

	/**
	 * Read session data
	 *
	 * @param   string  $id  The session id to read data for.
	 *
	 * @return  string  Returns an encoded string of the read data.
	 *                  If nothing was read, it must return an empty string.
	 *                  Note this value is returned internally to PHP for processing.
	 */
	public function read($id)
	{
		return null;
	}

	/**
	 * Write session data
	 *
	 * @param   string  $id    The session id.
	 * @param   string  $data  The encoded session data. This data is the
	 *                         result of the PHP internally encoding
	 *                         the $_SESSION super global to a serialized
	 *                         string and passing it as this parameter.
	 *                         Please note sessions use an alternative serialization method.
	 *
	 * @return   bool  The return value (usually TRUE on success, FALSE on failure).
	 *                 Note this value is returned internally to PHP for processing.
	 */
	public function write($id, $data)
	{
		return true;
	}
}
