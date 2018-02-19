<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
     * @see     getState()
     *
     * @since   2.0
     */
    protected $state;

    /**
     * The Cookie source, default will be $_COOKIE.
     *
     * Set this property to your array to help you test.
     *
     * @var  array
     *
     * @since  2.0
     */
    protected $cookie = null;

    /**
     * The session data bags.
     *
     * @var  SessionBagInterface[]
     */
    protected $bags = [];

    /**
     * The session bridge, default is PHP NativeBridge.
     *
     * Set this to your own bridge to help test.
     *
     * @var  SessionBridgeInterface
     */
    protected $bridge = null;

    /**
     * Session handler(storage).
     *
     * @var  HandlerInterface
     */
    protected $handler = null;

    /**
     * Session options.
     *
     * @var  array
     */
    protected $options = [];

    /**
     * Debug mode.
     *
     * @var boolean
     */
    protected $debug;

    /**
     * Session constructor.
     *
     * @param   HandlerInterface       $handler   The type of storage for the session.
     * @param   SessionBagInterface    $bag       The session data bags.
     * @param   FlashBagInterface      $flashBag  The session flash bags.
     * @param   SessionBridgeInterface $bridge    The session bridge, default is PHP NativeBridge.
     *                                            Set this to your own bridge to help test.
     * @param   array                  $options   Optional parameters.
     *
     * @since   2.0
     */
    public function __construct(
        HandlerInterface $handler = null,
        SessionBagInterface $bag = null,
        FlashBagInterface $flashBag = null,
        SessionBridgeInterface $bridge = null,
        array $options = []
    ) {
        $this->bridge = $bridge ?: new NativeBridge;

        // Create handler
        $this->handler = $handler ?: new NativeHandler;

        $bags = [
            'default' => $bag ?: new SessionBag,
            'flash' => $flashBag ?: new FlashBag,
        ];

        $this->setBags($bags);

        $this->options = $options;

        $this->init();

        $this->state = static::STATE_INACTIVE;
    }

    /**
     * Initialise Session params.
     *
     * @return  void
     */
    protected function init()
    {
        // Set name
        if ($this->getOption('name')) {
            $this->bridge->setName(md5($this->getOption('name')));
        }

        // Set id
        if ($this->getOption('id')) {
            $this->bridge->setId($this->getOption('id'));
        }

        // Sync the session maxlifetime
        if (!headers_sent()) {
            ini_set('session.gc_maxlifetime', $this->getOption('expire_time') * 60);
        }

        $this->setCookieParams();
    }

    /**
     * Get php cookie parameters.
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

        if ($this->getOption('force_ssl')) {
            $cookie['secure'] = true;
        }

        if ($this->getOption('cookie_domain')) {
            $cookie['domain'] = $this->getOption('cookie_domain');
        }

        if ($this->getOption('cookie_path')) {
            $cookie['path'] = $this->getOption('cookie_path');
        }

        $this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'],
            true);
    }

    /**
     * Start a session.
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public function start()
    {
        if ($this->state === static::STATE_ACTIVE) {
            return true;
        }

        $this->doStart();

        if ($this->state === static::STATE_EXPIRED) {
            $this->restart();
        }

        return true;
    }

    /**
     * doStart
     *
     * @return  boolean
     *
     * @since  3.0
     */
    protected function doStart()
    {
        $this->bridge->start();

        $this->prepareBagsData($this->bags);

        $this->state = static::STATE_ACTIVE;

        // Initialise the session
        $this->setCounter();
        $this->setTimers();

        // Perform security checks
        $this->validate();

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
        if ($this->state === static::STATE_DESTROYED) {
            return true;
        }

        /*
         * In order to kill the session altogether, such as to log the user out, the session id
         * must also be unset. If a cookie is used to propagate the session id (default behavior),
         * then the session cookie must be deleted.
         */
        if (isset($_COOKIE[$this->bridge->getName()])) {
            setcookie($this->bridge->getName(), '', time() - 42000, $this->getOption('cookie_path'),
                $this->getOption('cookie_domain'));
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

        if ($this->state !== static::STATE_DESTROYED) {
            throw new \RuntimeException('Session not destroyed, cannot restart.');
        }

        // Re-register the session handler after a session has been destroyed, to avoid PHP bug
        $this->registerHandler();

        $result = $this->doStart();

        $this->regenerate(true);

        return $result;
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
        if ($this->state !== static::STATE_ACTIVE) {
            throw new \RuntimeException('Session is not active.');
        }

        // Keep session config
        $cookie = $this->bridge->getCookieParams();

        // Kill session
        $this->bridge->destroy();

        // Re-register the session store after a session has been destroyed, to avoid PHP bug
        $this->registerHandler();

        // Restore config
        $this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'],
            true);

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
     * @return  static
     *
     * @see     session_write_close()
     * @since   2.0
     */
    public function close()
    {
        $this->bridge->save();

        $this->state = static::STATE_INACTIVE;

        return $this;
    }

    /**
     * Re generate the session id.
     *
     * @param   bool $destroy Destroy session or not.
     *
     * @return  static
     *
     * @since   2.0.4
     */
    public function regenerate($destroy = false)
    {
        $this->getBridge()->regenerate($destroy);

        return $this;
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
     * @param   boolean $restart Reactivate session
     *
     * @return  boolean  True on success
     *
     * @see     http://shiflett.org/articles/the-truth-about-sessions
     * @since   2.0
     */
    protected function validate($restart = false)
    {
        // Allow to restart a session
        if ($restart) {
            $this->state = static::STATE_ACTIVE;

            $this->set('session.client.address', null);
            $this->set('session.client.forwarded', null);
            $this->set('session.client.browser', null);
            $this->set('session.token', null);
        }

        // Check if session has expired
        if ($this->getOption('expire_time')) {
            $curTime = $this->get('session.timer.now', 0);
            $maxTime = $this->get('session.timer.last', 0) + ($this->getOption('expire_time') * 60);

            // Empty session variables
            if ($maxTime < $curTime) {
                $this->state = static::STATE_EXPIRED;

                return false;
            }
        }

        // Record proxy forwarded for in the session in case we need it later
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        // Check for client address
        if ($this->getOption('fix_address') && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $this->get('session.client.address');

            if ($ip === null) {
                $this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
            } elseif ($_SERVER['REMOTE_ADDR'] !== $ip) {
                $this->state = static::STATE_ERROR;

                return false;
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
        if (!$this->exists('session.timer.start')) {
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
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
                throw new \RuntimeException('Session is not active.');
            }

            return false;
        }

        return $this->getBag($namespace)->get($name, $default);
    }

    /**
     * Get all session data.
     *
     * @param   string $namespace Session namespace, default is `default`.
     *
     * @return  array
     * @throws \RuntimeException
     *
     * @since   2.0
     */
    public function getAll($namespace = 'default')
    {
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
                throw new \RuntimeException('Session is not active.');
            }

            return [];
        }

        return $this->getBag($namespace)->all();
    }

    /**
     * Get all session data and clean them.
     *
     * @param   string $namespace Session namespace, default is `default`.
     *
     * @return  array
     * @throws \RuntimeException
     *
     * @since   2.0.4
     */
    public function takeAll($namespace = 'default')
    {
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
                throw new \RuntimeException('Session is not active.');
            }

            return false;
        }

        $bag = $this->getBag($namespace);
        $all = $bag->all();

        $this->clean($namespace);

        return $all;
    }

    /**
     * Clean all data from a bag (namespace).
     *
     * @param   string $namespace $namespace  Session namespace, default is `default`.
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.0.4
     */
    public function clean($namespace = 'default')
    {
        $this->getBag($namespace)->clear();

        return $this;
    }

    /**
     * Set data into the session store.
     *
     * @param   string $name      Name of a variable.
     * @param   mixed  $value     Value of a variable.
     * @param   string $namespace Namespace to use, default to 'default'.
     *
     * @throws \RuntimeException
     * @return  static  Return self to support chaining.
     *
     * @since   2.0
     */
    public function set($name, $value = null, $namespace = 'default')
    {
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
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
     * @throws  \RuntimeException
     * @return  boolean  True if the variable exists
     *
     * @since   2.0
     */
    public function exists($name, $namespace = 'default')
    {
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
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
     */
    public function remove($name, $namespace = 'default')
    {
        if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED) {
            if ($this->debug) {
                throw new \RuntimeException('Session is not active.');
            }

            return false;
        }

        $this->getBag($namespace)->set($name, null);

        return $this;
    }

    /**
     * Add a flash message.
     *
     * @param array|string $msg  The message you want to set, can be an array to storage multiple messages.
     * @param string       $type The message type, default is `info`.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function addFlash($msg, $type = 'info')
    {
        $this->getFlashBag()->add($msg, $type);

        return $this;
    }

    /**
     * Take all flashes and clean them from bag.
     *
     * @return  array  All flashes data.
     *
     * @since   2.0
     */
    public function getFlashes()
    {
        return $this->getFlashBag()->takeAll();
    }

    /**
     * Retrieve an external iterator
     *
     * @param   string $namespace The namespace to get data.
     *
     * @return  Traversable An instance of an object implementing Iterator Traversable
     *
     * @since   2.0
     */
    public function getIterator($namespace = 'default')
    {
        $array = (array)$this->getAll($namespace);

        return new \ArrayIterator($array);
    }

    /**
     * Method to get property Bridge
     *
     * @return  SessionBridgeInterface
     *
     * @since   2.0
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
     *
     * @since   2.0
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
     *
     * @since   2.0
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
     *
     * @since   2.0
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
        return (bool)($this->state === static::STATE_ACTIVE);
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

        return (bool)($counter === 1);
    }

    /**
     * Create a token-string
     *
     * @param   integer $length Length of string
     *
     * @return  string  Generated token
     *
     * @since   2.0
     */
    protected function createToken($length = 32)
    {
        static $chars = '0123456789abcdef';
        $max   = strlen($chars) - 1;
        $token = '';
        $name  = $this->getName();

        for ($i = 0; $i < $length; ++$i) {
            $token .= $chars[(mt_rand(0, $max))];
        }

        return md5($token . $name);
    }

    /**
     * Method to get property State
     *
     * @return  string
     *
     * @since   2.0
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
     *
     * @since   2.0
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get cookie source, default is $_COOKIE.
     *
     * @return  array  Cookie source
     *
     * @since   2.0
     */
    public function getCookie()
    {
        if ($this->cookie === null) {
            $this->cookie = &$_COOKIE;
        }

        return $this->cookie;
    }

    /**
     * Set cookie source. default will be $_COOKIE.
     *
     * Set this property to your array to help you test.
     *
     * @param   array $cookie Cookie source.
     *
     * @return  Session  Return self to support chaining.
     *
     * @since   2.0
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
     *
     * @since   2.0
     */
    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * Method to set property options
     *
     * @param   string $name
     * @param   mixed  $value
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.0
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
     *
     * @since   2.0
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
     *
     * @since   2.0
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Register handler as PHP session handler.
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.0
     */
    protected function registerHandler()
    {
        $this->handler->register();

        return $this;
    }

    /**
     * Get all Session bags.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getBags()
    {
        return $this->bags;
    }

    /**
     * Set Session bags.
     *
     * @param   SessionBagInterface[] $bags
     *
     * @return  Session  Return self to support chaining.
     *
     * @since   2.0
     */
    public function setBags(array $bags)
    {
        foreach ($bags as $name => $bag) {
            $this->setBag($name, $bag);
        }

        return $this;
    }

    /**
     * Get session bag.
     *
     * @param   string $name Bag name to get.
     *
     * @throws  \UnexpectedValueException
     * @return  SessionBagInterface
     *
     * @since   2.0
     */
    public function getBag($name)
    {
        $name = strtolower($name);

        if (empty($this->bags[$name])) {
            throw new \UnexpectedValueException(sprintf('Bag %s not exists', $name));
        }

        return $this->bags[$name];
    }

    /**
     * Set session bag by name.
     *
     * @param   string              $name Session bag name to set.
     * @param   SessionBagInterface $bag  Session bag object.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function setBag($name, SessionBagInterface $bag)
    {
        $this->bags[strtolower($name)] = $bag;

        if ($this->isActive()) {
            $this->prepareBagsData([$name => $bag]);
        }

        return $this;
    }

    /**
     * Get Flash bag.
     *
     * @return  FlashBagInterface
     *
     * @since   2.0
     */
    public function getFlashBag()
    {
        if (empty($this->bags['flash'])) {
            $this->bags['flash'] = new FlashBag;
        }

        return $this->bags['flash'];
    }

    /**
     * Set Flash Bag
     *
     * @param   FlashBagInterface $bag The flash bag object.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function setFlashBag(FlashBagInterface $bag)
    {
        $this->bags['flash'] = $bag;

        return $this;
    }

    /**
     * preapreBagsData
     *
     * @param   SessionBagInterface[] $bags
     *
     * @return  static
     *
     * @since   2.0
     */
    protected function prepareBagsData(array $bags)
    {
        foreach ($bags as $name => $bag) {
            $ns = '_' . strtolower($name);

            $session = &$this->getBridge()->getStorage();

            if (!isset($session[$ns]) || !is_array($session[$ns])) {
                $session[$ns] = [];
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
     *
     * @since   2.0
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Is a property exists or not.
     *
     * @param   mixed $offset Offset key.
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * Get a property.
     *
     * @param   mixed $offset Offset key.
     *
     * @throws  \InvalidArgumentException
     * @return  mixed The value to return.
     *
     * @since   2.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set a value to property.
     *
     * @param   mixed $offset Offset key.
     * @param   mixed $value  The value to set.
     *
     * @throws  \InvalidArgumentException
     * @return  void
     *
     * @since   2.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset a property.
     *
     * @param   mixed $offset Offset key to unset.
     *
     * @throws  \InvalidArgumentException
     * @return  void
     *
     * @since   2.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
