<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session;

use Traversable;
use Windwalker\Session\Bag\FlashBag;
use Windwalker\Session\Bag\FlashBagInterface;
use Windwalker\Session\Bag\SessionBag;
use Windwalker\Session\Bag\SessionBagInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\SessionBridgeInterface;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\NativeHandler;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @since  2.0
 */
class Session implements \ArrayAccess, \IteratorAggregate
{
	const STATE_ACTIVE = 'active';

	const STATE_INACTIVE = 'inactive';

	const STATE_EXPIRED = 'expired';

	const STATE_DESTROYED = 'destroyed';

	const STATE_ERROR = 'error';

	/**
	 * Internal state.
	 * One of 'inactive'|'active'|'expired'|'destroyed'|'error'
	 *
	 * @var  string
	 * @see  getState()
	 */
	protected $state;

	/**
	 * Property cookie.
	 *
	 * @var  array
	 */
	protected $cookie = null;

	/**
	 * Property bags.
	 *
	 * @var  SessionBagInterface[]
	 */
	protected $bags = array();

	/**
	 * Property bridge.
	 *
	 * @var  SessionBridgeInterface
	 */
	protected $bridge = null;

	/**
	 * Property handler.
	 *
	 * @var  HandlerInterface
	 */
	protected $handler;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Property debug.
	 *
	 * @var boolean
	 */
	protected $debug;

	/**
	 * Constructor
	 *
	 * @param   HandlerInterface       $handler The type of storage for the session.
	 * @param   SessionBagInterface    $bag
	 * @param   FlashBagInterface      $flashBag
	 * @param   SessionBridgeInterface $bridge
	 * @param   array                  $options Optional parameters
	 *
	 * @since   2.0
	 */
	public function __construct(HandlerInterface $handler = null, SessionBagInterface $bag = null,
		FlashBagInterface $flashBag = null, SessionBridgeInterface $bridge = null, array $options = array())
	{
		$this->bridge = $bridge ? : new NativeBridge;

		// Create handler
		$this->handler = $handler ? : new NativeHandler;

		$bags = array(
			'default' => $bag ? : new SessionBag,
			'flash'   => $flashBag ? : new FlashBag
		);

		$this->setBags($bags);

		$this->options = $options;

		$this->init();

		$this->state = static::STATE_INACTIVE;
	}

	/**
	 * init
	 *
	 * @return  void
	 */
	protected function init()
	{
		// Set name
		if ($this->getOption('name'))
		{
			$this->bridge->setName(md5($this->getOption('name')));
		}

		// Set id
		if ($this->getOption('id'))
		{
			$this->bridge->setId($this->getOption('id'));
		}

		// Sync the session maxlifetime
		ini_set('session.gc_maxlifetime', $this->getOption('expire_time') * 60);

		$this->setCookieParams();
	}

	/**
	 * getCookieParams
	 *
	 * @return  array
	 */
	protected function getCookieParams()
	{
		return $this->bridge->getCookieParams();
	}

	/**
	 * Set session cookie parameters, this method should call before session started.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function setCookieParams()
	{
		$cookie = $this->getCookieParams();

		if ($this->getOption('force_ssl'))
		{
			$cookie['secure'] = true;
		}

		if ($this->getOption('cookie_domain'))
		{
			$cookie['domain'] = $this->getOption('cookie_domain');
		}

		if ($this->getOption('cookie_path'))
		{
			$cookie['path'] = $this->getOption('cookie_path');
		}

		$this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);
	}

	/**
	 * Start a session.
	 *
	 * @param bool $restart
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function start($restart = false)
	{
		if ($this->state === static::STATE_ACTIVE)
		{
			return true;
		}

		// Start session if not started
		if ($restart)
		{
			$this->bridge->regenerate(true);
		}
		else
		{
			$sessionName = $this->bridge->getName();

			$cookie = $this->getCookie();

			// If cookie do not have session id, try to get it from http queries.
			if (empty($cookie[$sessionName]))
			{
				$sessionClean = isset($_GET[$sessionName]) ? $_GET[$sessionName] :  false;

				if ($sessionClean)
				{
					$this->bridge->getId($sessionClean);
					setcookie($sessionName, '', time() - 3600);
					$cookie[$sessionName] = '';
				}
			}
		}

		$this->bridge->start();

		$this->prepareBagsData($this->bags);

		$this->state = static::STATE_ACTIVE;

		// Initialise the session
		$this->setCounter();
		$this->setTimers();

		// Perform security checks
		$this->validate();

		if (!$restart && $this->state === static::STATE_EXPIRED)
		{
			$this->restart();
		}

		return true;
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the $_SESSION variable and destroys all of the data associated
	 * with the current session in its storage (file or DB). It forces new session to be
	 * started after this method is called. It does not unset the session cookie.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   2.0
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->state === static::STATE_DESTROYED)
		{
			return true;
		}

		/*
		 * In order to kill the session altogether, such as to log the user out, the session id
		 * must also be unset. If a cookie is used to propagate the session id (default behavior),
		 * then the session cookie must be deleted.
		 */
		if (isset($_COOKIE[$this->bridge->getName()]))
		{
			setcookie($this->bridge->getName(), '', time() - 42000, $this->getOption('cookie_path'), $this->getOption('cookie_domain'));
		}

		$this->bridge->destroy();

		$this->state = static::STATE_DESTROYED;

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @throws \RuntimeException
	 * @return  boolean  True on success
	 *
	 * @see     destroy
	 * @since   2.0
	 */
	public function restart()
	{
		$this->destroy();

		if ($this->state !== static::STATE_DESTROYED)
		{
			throw new \RuntimeException('Session not destroyed, cannot restart.');
		}

		// Re-register the session handler after a session has been destroyed, to avoid PHP bug
		$this->registerHandler();

		return $this->start(true);
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @throws \RuntimeException
	 * @return  boolean $result true on success
	 *
	 * @since   2.0
	 */
	public function fork()
	{
		if ($this->state !== static::STATE_ACTIVE)
		{
			throw new \RuntimeException('Session is not active.');
		}

		// Keep session config
		$cookie = $this->bridge->getCookieParams();

		// Kill session
		$this->bridge->destroy();

		// Re-register the session store after a session has been destroyed, to avoid PHP bug
		$this->registerHandler();

		// Restore config
		$this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);

		// Restart session with new id
		$this->bridge->restart(true);

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call close(), but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   2.0
	 */
	public function close()
	{
		$this->bridge->save();
	}

	/**
	 * Do some checks for security reason
	 *
	 * - timeout check (expire)
	 * - ip-fixiation
	 * - browser-fixiation
	 *
	 * If one check failed, session data has to be cleaned.
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  boolean  True on success
	 *
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   2.0
	 */
	protected function validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->state = static::STATE_ACTIVE;

			$this->set('session.client.address', null);
			$this->set('session.client.forwarded', null);
			$this->set('session.client.browser', null);
			$this->set('session.token', null);
		}

		// Check if session has expired
		if ($this->getOption('expire_time'))
		{
			$curTime = $this->get('session.timer.now', 0);
			$maxTime = $this->get('session.timer.last', 0) + ($this->getOption('expire_time') * 60);

			// Empty session variables
			if ($maxTime < $curTime)
			{
				$this->state = static::STATE_EXPIRED;

				return false;
			}
		}

		// Record proxy forwarded for in the session in case we need it later
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		// Check for client address
		if ($this->getOption('fix_address') && isset($_SERVER['REMOTE_ADDR']))
		{
			$ip = $this->get('session.client.address');

			if ($ip === null)
			{
				$this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
			}
			elseif ($_SERVER['REMOTE_ADDR'] !== $ip)
			{
				$this->state = static::STATE_ERROR;

				return false;
			}
		}

		// Check for clients browser
		if ($this->getOption('fix_browser', true) && isset($_SERVER['HTTP_USER_AGENT']))
		{
			$browser = $this->get('session.client.browser');

			if ($browser === null)
			{
				$this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);
			}
			elseif ($_SERVER['HTTP_USER_AGENT'] !== $browser)
			{
				// Nothing to do.
			}
		}

		return true;
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  static Return self to support chaining.
	 *
	 * @since   2.0
	 */
	protected function setCounter()
	{
		$counter = $this->get('session.counter', 0);

		++$counter;

		$this->set('session.counter', $counter);

		return $this;
	}

	/**
	 * Set the session timers
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	protected function setTimers()
	{
		if (!$this->has('session.timer.start'))
		{
			$start = time();

			$this->set('session.timer.start', $start);
			$this->set('session.timer.last', $start);
			$this->set('session.timer.now', $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return $this;
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string $name      Name of a variable
	 * @param   mixed  $default   Default value of a variable if not set
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws  \RuntimeException
	 * @return  mixed  Value of a variable
	 *
	 * @since   2.0
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			if ($this->debug)
			{
				throw new \RuntimeException('Session is not active.');
			}
		}

		return $this->getBag($namespace)->get($name, $default);
	}

	/**
	 * getAll
	 *
	 * @param string $namespace
	 *
	 * @return  mixed
	 *
	 * @throws \RuntimeException
	 */
	public function getAll($namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			if ($this->debug)
			{
				throw new \RuntimeException('Session is not active.');
			}

			return false;
		}

		return $this->getBag($namespace)->all();
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string $name      Name of a variable.
	 * @param   mixed  $value     Value of a variable.
	 * @param   string $namespace Namespace to use, default to 'default'.
	 *
	 * @throws \RuntimeException
	 * @return  Session
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			if ($this->debug)
			{
				throw new \RuntimeException('Session is not active. Now is: ' . $this->state);
			}

			return false;
		}

		$this->getBag($namespace)->set($name, $value);

		return $this;
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   2.0
	 *
	 * @deprecated  Use exists() instead.
	 */
	public function has($name, $namespace = 'default')
	{
		return $this->exists($name, $namespace);
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws  \RuntimeException
	 * @return  boolean  True if the variable exists
	 *
	 * @since   2.0
	 */
	public function exists($name, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			if ($this->debug)
			{
				throw new \RuntimeException('Session is not active.');
			}

			return false;
		}

		return $this->getBag($namespace)->has($name);
	}

	/**
	 * Unset data from the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws \RuntimeException
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   2.0
	 *
	 * @deprecated  Use remove() instead.
	 */
	public function clear($name, $namespace = 'default')
	{
		return $this->remove($name, $namespace);
	}

	/**
	 * Unset data from the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws \RuntimeException
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   2.0
	 */
	public function remove($name, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			if ($this->debug)
			{
				throw new \RuntimeException('Session is not active.');
			}

			return false;
		}

		$this->getBag($namespace)->set($name, null);

		return $this;
	}

	/**
	 * addFlash
	 *
	 * @param array|string $msg
	 * @param string       $type
	 *
	 * @return  Session
	 */
	public function addFlash($msg, $type = 'info')
	{
		$this->getFlashBag()->add($msg, $type);

		return $this;
	}

	/**
	 * takeFlashes
	 *
	 * @return  array
	 */
	public function getFlashes()
	{
		return $this->getFlashBag()->takeAll();
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @param  string  $namespace
	 *
	 * @return Traversable An instance of an object implementing Iterator Traversable
	 */
	public function getIterator($namespace = 'default')
	{
		return new \ArrayIterator($this->getAll($namespace));
	}

	/**
	 * Method to get property Bridge
	 *
	 * @return  SessionBridgeInterface
	 */
	public function getBridge()
	{
		return $this->bridge;
	}

	/**
	 * Method to set property bridge
	 *
	 * @param   SessionBridgeInterface $bridge
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setBridge($bridge)
	{
		$this->bridge = $bridge;

		return $this;
	}

	/**
	 * Method to get property Handler
	 *
	 * @return  HandlerInterface
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Method to set property handler
	 *
	 * @param   HandlerInterface $handler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Get session name
	 *
	 * @return  string  The session name
	 *
	 * @since   2.0
	 */
	public function getName()
	{
		return $this->bridge->getName();
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session name
	 *
	 * @since   2.0
	 */
	public function getId()
	{
		return $this->bridge->getId();
	}

	/**
	 * Shorthand to check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function isActive()
	{
		return (bool) ($this->state === static::STATE_ACTIVE);
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.0
	 */
	public function isNew()
	{
		$counter = $this->get('session.counter');

		return (bool) ($counter === 1);
	}

	/**
	 * Create a token-string
	 *
	 * @param   integer  $length  Length of string
	 *
	 * @return  string  Generated token
	 *
	 * @since   2.0
	 */
	protected function createToken($length = 32)
	{
		static $chars = '0123456789abcdef';
		$max = strlen($chars) - 1;
		$token = '';
		$name = $this->getName();

		for ($i = 0; $i < $length; ++$i)
		{
			$token .= $chars[(rand(0, $max))];
		}

		return md5($token . $name);
	}

	/**
	 * Method to get property State
	 *
	 * @return  string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Method to set property state
	 *
	 * @param   string $state
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * getCookie
	 *
	 * @return  array
	 */
	public function getCookie()
	{
		if ($this->cookie === null)
		{
			$this->cookie = &$_COOKIE;
		}

		return $this->cookie;
	}

	/**
	 * setCookie
	 *
	 * @param   array  $cookie
	 *
	 * @return  Session  Return self to support chaining.
	 */
	public function setCookie($cookie)
	{
		$this->cookie = $cookie;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @param   string $name
	 * @param   mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		if (array_key_exists($name, $this->options))
		{
			return $this->options[$name];
		}

		return $default;
	}

	/**
	 * Method to set property options
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @return  array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * registerHandler
	 *
	 * @return  void
	 */
	protected function registerHandler()
	{
		$this->handler->register();
	}

	/**
	 * getBags
	 *
	 * @return  array
	 */
	public function getBags()
	{
		return $this->bags;
	}

	/**
	 * setBags
	 *
	 * @param   SessionBagInterface[] $bags
	 *
	 * @return  Session  Return self to support chaining.
	 */
	public function setBags(array $bags)
	{
		foreach ($bags as $name => $bag)
		{
			$this->setBag($name, $bag);
		}

		return $this;
	}

	/**
	 * getBag
	 *
	 * @param string $name
	 *
	 * @throws  \UnexpectedValueException
	 * @return  SessionBagInterface
	 */
	public function getBag($name)
	{
		$name = strtolower($name);

		if (empty($this->bags[$name]))
		{
			throw new \UnexpectedValueException(sprintf('Bag %s not exists', $name));
		}

		return $this->bags[$name];
	}

	/**
	 * setBag
	 *
	 * @param string              $name
	 * @param SessionBagInterface $bag
	 *
	 * @return  Session
	 */
	public function setBag($name, SessionBagInterface $bag)
	{
		$this->bags[strtolower($name)] = $bag;

		if ($this->isActive())
		{
			$this->prepareBagsData(array($name => $bag));
		}

		return $this;
	}

	/**
	 * getFlashBag
	 *
	 * @return  FlashBagInterface
	 */
	public function getFlashBag()
	{
		if (empty($this->bags['flash']))
		{
			$this->bags['flash'] = new FlashBag;
		}

		return $this->bags['flash'];
	}

	/**
	 * setFlashBag
	 *
	 * @param   FlashBagInterface $bag
	 *
	 * @return  Session
	 */
	public function setFlashBag(FlashBagInterface $bag)
	{
		$this->bags['flash'] = $bag;

		return $this;
	}

	/**
	 * preapreBagsData
	 *
	 * @param SessionBagInterface[] $bags
	 *
	 * @return  Session
	 */
	protected function prepareBagsData(array $bags)
	{
		foreach ($bags as $name => $bag)
		{
			$ns = '_' . strtolower($name);

			$session = &$_SESSION;

			if (!isset($session[$ns]) || !is_array($session[$ns]))
			{
				$session[$ns] = array();
			}

			$bag->setData($session[$ns]);
		}

		return $this;
	}

	/**
	 * Method to set property debug
	 *
	 * @param   boolean $debug
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;

		return $this;
	}

	/**
	 * Is a property exists or not.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return $this->exists($offset);
	}

	/**
	 * Get a property.
	 *
	 * @param mixed $offset Offset key.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  mixed The value to return.
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Set a value to property.
	 *
	 * @param mixed $offset Offset key.
	 * @param mixed $value  The value to set.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Unset a property.
	 *
	 * @param mixed $offset Offset key to unset.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  void
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}
