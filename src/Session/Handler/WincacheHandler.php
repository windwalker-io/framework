<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Class WincacheHandler
 *
 * @since {DEPLOY_VERSION}
 */
class WincacheHandler implements HandlerInterface
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   {DEPLOY_VERSION}
	 * @throws  \RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!static::isSupported())
		{
			throw new \RuntimeException('Wincache Extension is not available', 404);
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	static public function isSupported()
	{
		return (extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), "1"));
	}

	/**
	 * register
	 *
	 * @return  mixed|void
	 */
	public function register()
	{
		ini_set('session.save_handler', 'wincache');
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
	 * Reads the session data.
	 *
	 * @see http://php.net/sessionhandlerinterface.read
	 *
	 * @param string $sessionId Session ID, see http://php.net/function.session-id
	 *
	 * @return string Same session data as passed in write() or empty string when non-existent or on failure
	 */
	public function read($sessionId)
	{
		return '';
	}

	/**
	 * Writes the session data to the storage.
	 *
	 * @see http://php.net/sessionhandlerinterface.write
	 *
	 * @param string $sessionId Session ID , see http://php.net/function.session-id
	 * @param string $data      Serialized session data to save
	 *
	 * @return bool true on success, false on failure
	 */
	public function write($sessionId, $data)
	{
		return true;
	}

	/**
	 * Destroys a session.
	 *
	 * @see http://php.net/sessionhandlerinterface.destroy
	 *
	 * @param string $sessionId Session ID, see http://php.net/function.session-id
	 *
	 * @return bool true on success, false on failure
	 */
	public function destroy($sessionId)
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

