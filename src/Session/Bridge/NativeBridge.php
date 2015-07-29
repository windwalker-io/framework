<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Bridge;

/**
 * The SessionBridge class.
 * 
 * @since  2.0
 */
class NativeBridge implements SessionBridgeInterface
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
	 * Class init.
	 */
	public function __construct()
	{
		// Disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		// Only allow the session ID to come from cookies and nothing else.
		ini_set('session.use_only_cookies', '1');
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
		/*
		 * Write and Close handlers are called after destructing objects since PHP 5.0.5.
		 * Thus destructors can use sessions but session handler can't use objects.
		 * So we are moving session closure before destructing objects.
		 */
		if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			session_register_shutdown();
		}
		else
		{
			register_shutdown_function('session_write_close');
		}

		session_cache_limiter('none');

		if (!session_start())
		{
			throw new \RuntimeException('Failed to start the session');
		}

		$this->started = true;

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
		return session_id();
	}

	/**
	 * Sets the session ID
	 *
	 * @param   string  $id  Set the session id
	 *
	 * @return  void
	 */
	public function setId($id)
	{
		session_id($id);
	}

	/**
	 * Returns the session name
	 *
	 * @return  mixed   The session name.
	 */
	public function getName()
	{
		return session_name();
	}

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  Set the name of the session
	 *
	 * @return  void
	 */
	public function setName($name)
	{
		if ($this->isStarted())
		{
			throw new \LogicException('Cannot change the name of an active session');
		}

		session_name($name);
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
	 * @param   bool  $destroy   Destroy session when regenerating?
	 * @param   int   $lifetime  Sets the cookie lifetime for the session cookie. A null value
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
		if (null !== $lifetime)
		{
			ini_set('session.cookie_lifetime', $lifetime);
		}

		$return = $this->regenerate($destroy);

		// Workaround for https://bugs.php.net/bug.php?id=61470 as suggested by David Grudl
		session_write_close();

		if (isset($_SESSION))
		{
			$backup = $_SESSION;
			$this->start();
			$_SESSION = $backup;
		}
		else
		{
			$this->start();
		}

		return $return;
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
		return session_regenerate_id($destroy);
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
		session_write_close();

		$this->closed = true;
		$this->started = false;
	}

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 */
	public function destroy()
	{
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id())
		{
			session_unset();
			session_destroy();
		}

		$this->closed = true;
		$this->started = false;
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
	 * @param   integer  $lifetime  Lifetime of the session cookie, defined in seconds.
	 * @param   string   $path      Path on the domain where the cookie will work. Use a single
	 *                              slash ('/') for all paths on the domain.
	 * @param   string   $domain    Cookie domain, for example 'www.php.net'. To make cookies
	 *                              visible on all sub domains then the domain must be prefixed
	 *                              with a dot like '.php.net'.
	 * @param   boolean  $secure    If true cookie will only be sent over secure connections.
	 * @param   boolean  $httponly  If set to true then PHP will attempt to send the httponly
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
}
