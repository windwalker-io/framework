<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Test\Mock;

use Windwalker\Session\Bridge\SessionBridgeInterface;

/**
 * The MockArrayBridge class.
 *
 * @since  2.0
 */
class MockArrayBridge implements SessionBridgeInterface
{
	/**
	 * Property started.
	 *
	 * @var boolean
	 */
	protected $started;

	/**
	 * Property closed.
	 *
	 * @var boolean
	 */
	protected $closed;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Property id.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Property data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param string      $name    Session name
	 */
	public function __construct($name = 'MOCKSESSID')
	{
		$this->name = $name;

		$this->id = $this->generateId($name);
	}

	/**
	 * Starts the session.
	 *
	 * @return  bool  True if started.
	 *
	 * @throws \RuntimeException If something goes wrong starting the session.
	 */
	public function start()
	{
		if ($this->started && !$this->closed)
		{
			return true;
		}

		if (empty($this->id))
		{
			$this->setId($this->generateId());
		}

		return true;
	}

	/**
	 * Checks if the session is started.
	 *
	 * @return  bool  True if started, false otherwise.
	 */
	public function isStarted()
	{
		return $this->started;
	}

	/**
	 * Returns the session ID
	 *
	 * @return  string  The session ID or empty.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets the session ID
	 *
	 * @param   string $id Set the session id
	 *
	 * @return  void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns the session name
	 *
	 * @return  mixed   The session name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the session name
	 *
	 * @param   string $name Set the name of the session
	 *
	 * @return  void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Regenerates id that represents this storage.
	 *
	 * This method must invoke session_regenerate_id($destroy) unless
	 * this interface is used for a storage object designed for unit
	 * or functional testing where a real PHP session would interfere
	 * with testing.
	 *
	 * Note regenerate+destroy should not clear the session data in memory
	 * only delete the session data from persistent storage.
	 *
	 * @param   bool $destroy    Destroy session when regenerating?
	 * @param   int  $lifetime   Sets the cookie lifetime for the session cookie. A null value
	 *                           will leave the system settings unchanged, 0 sets the cookie
	 *                           to expire with browser session. Time is in seconds, and is
	 *                           not a Unix timestamp.
	 *
	 * @return  bool  True if session regenerated, false if error
	 *
	 * @throws  \RuntimeException  If an error occurs while regenerating this storage
	 */
	public function restart($destroy = false, $lifetime = null)
	{
		if (!$this->started)
		{
			$this->start();
		}

		$this->id = $this->generateId();

		return true;
	}

	/**
	 * regenerate
	 *
	 * @param bool $destroy
	 *
	 * @return  bool
	 */
	public function regenerate($destroy = false)
	{
		return $this->generateId();
	}

	/**
	 * Force the session to be saved and closed.
	 *
	 * This method must invoke session_write_close() unless this interface is
	 * used for a storage object design for unit or functional testing where
	 * a real PHP session would interfere with testing, in which case it
	 * it should actually persist the session data if required.
	 *
	 * @return  void
	 *
	 * @throws \RuntimeException If the session is saved without being started, or if the session
	 *                           is already closed.
	 */
	public function save()
	{
		if (!$this->started || $this->closed)
		{
			throw new \RuntimeException("Trying to save a session that was not started yet or was already closed");
		}

		// Nothing to do since we don't persist the session data
		$this->closed = false;
		$this->started = false;
	}

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 */
	public function destroy()
	{
		// Clear out the session
		$this->data = array();
	}

	/**
	 * getCookieParams
	 *
	 * @return  array
	 */
	public function getCookieParams()
	{
		return session_get_cookie_params();
	}

	/**
	 * Set session cookie parameters, this method should call before session started.
	 *
	 * @param   integer $lifetime   Lifetime of the session cookie, defined in seconds.
	 * @param   string  $path       Path on the domain where the cookie will work. Use a single
	 *                              slash ('/') for all paths on the domain.
	 * @param   string  $domain     Cookie domain, for example 'www.php.net'. To make cookies
	 *                              visible on all sub domains then the domain must be prefixed
	 *                              with a dot like '.php.net'.
	 * @param   boolean $secure     If true cookie will only be sent over secure connections.
	 * @param   boolean $httponly   If set to true then PHP will attempt to send the httponly
	 *                              flag when setting the session cookie.
	 *
	 * @return  static
	 *
	 * @since   2.0
	 */
	public function setCookieParams($lifetime, $path = null, $domain = null, $secure = false, $httponly = true)
	{
		session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

		return $this;
	}

	/**
	 * Generates a session ID.
	 *
	 * This doesn't need to be particularly cryptographically secure since this is just
	 * a mock.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function generateId($name = 'PHPSESSID')
	{
		return md5($name);
	}
}
