<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

/**
 * SessionHandler Interface
 *
 * PHP 5.3 compatibility for PHP 5.4's \SessionHandlerInterface
 *
 * @link   http://php.net/manual/en/class.sessionhandlerinterface.php
 *
 * @since  {DEPLOY_VERSION}
 */
interface SessionHandlerInterface
{
	/**
	 * Close the session
	 *
	 * @return bool The return value (usually TRUE on success, FALSE on failure).
	 *              Note this value is returned internally to PHP for processing.
	 */
	public function close();

	/**
	 * Destroy a session
	 *
	 * @param   int   $id  The session ID being destroyed.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function destroy($id);

	/**
	 * Cleanup old sessions
	 *
	 * @param   int  $maxlifetime  Sessions that have not updated for
	 *                             the last maxlifetime seconds will be removed.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function gc($maxlifetime);

	/**
	 * Initialize session
	 *
	 * @param   string  $savePath  The path where to store/retrieve the session.
	 * @param   string  $id        The session id.
	 *
	 * @return  bool  The return value (usually TRUE on success, FALSE on failure).
	 *                Note this value is returned internally to PHP for processing.
	 */
	public function open($savePath, $id);

	/**
	 * Read session data
	 *
	 * @param   string  $id  The session id to read data for.
	 *
	 * @return  string  Returns an encoded string of the read data.
	 *                  If nothing was read, it must return an empty string.
	 *                  Note this value is returned internally to PHP for processing.
	 */
	public function read($id);

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
	public function write($id, $data);
}
