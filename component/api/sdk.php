<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

class AKRequestSDK extends JObject
{
	/**
	 * @var string
	 */
	protected $username = "";

	/**
	 * @var string
	 */
	protected $password = "";

	/**
	 * @var
	 */
	protected static $instances;

	/**
	 * @var string
	 */
	public $host = '';

	/**
	 * @var JURI|string
	 */
	public $uri = '';

	/**
	 * @var array
	 */
	public $uri_cache = array();

	/**
	 * @var array
	 */
	public $result = array();

	/**
	 * @var bool
	 */
	protected $relogin = false;

	/**
	 * @var bool|mixed|string
	 */
	protected $session_key = '';

	/**
	 * @var string
	 */
	protected $session_cache_path = '';

	/**
	 * @var string
	 */
	protected $session_cache_file = '';

	/**
	 * @var bool
	 */
	public $isLogin = true;

	/**
	 * @var bool
	 */
	public $forceJson = false;

	/**
	 * Construct
	 *
	 * @param mixed|null $option
	 */
	public function __construct($option)
	{
		$this->uri = new JURI();
		$this->setHost($option['host']);

		$ssl = JArrayHelper::getValue($option, 'ssl', false);
		$this->useSSL($ssl);

		// User Info
		$this->username = !empty($option['username']) ? $option['username'] : $this->username;
		$this->password = !empty($option['password']) ? $option['password'] : $this->password;

		// Get Session Key
		$this->session_cache_path = JPATH_ROOT . '/cache/AKRequestSDK';
		$this->session_cache_file = $this->session_cache_path . '/session_key';

		$this->session_key = $this->getSessionKey();
	}

	/**
	 * getInstance
	 *
	 * @param $option
	 *
	 * @return mixed
	 */
	public static function getInstance($option)
	{
		$hash = AKHelper::_('system.uuid', $option['host'], 3);

		if (!empty(self::$instances[$hash]))
		{
			return $instances;
		}

		self::$instances[$hash] = new AKRequestSDK($option);

		return self::$instances[$hash];
	}

	/**
	 * setHost
	 *
	 * @param $host
	 *
	 * @return void
	 */
	public function setHost($host)
	{
		$uri        = new JURI($host);
		$this->host = $host = $uri->getHost() . $uri->getPath();

		$this->uri->setHost($host);
	}

	/**
	 * getHost
	 *
	 * @param $host
	 *
	 * @return string
	 */
	public function getHost($host)
	{
		return $this->host;
	}

	/**
	 * useSSL
	 *
	 * @param $ssl
	 *
	 * @return void
	 */
	public function useSSL($ssl)
	{
		if ($ssl)
		{
			$this->uri->setScheme('https');
		}
		else
		{
			$this->uri->setScheme('http');
		}
	}

	/**
	 * execute
	 *
	 * @param        $path
	 * @param string $query
	 * @param string $method
	 * @param string $type
	 *
	 * @return bool
	 */
	public function execute($path, $query = '', $method = 'get', $type = 'object')
	{
		// Set Session Key
		$query                = (array) $query;
		$query['session_key'] = $this->session_key;

		// Do Execute
		$result = $this->doExecute($path, $query, $method, $type);

		// If not login or session expire, relogin.
		if (!$result)
		{
			if ($this->relogin)
			{
				// Do Login
				$login_result = $this->login();

				if (!$login_result)
				{
					return false;
				}

				$this->isLogin = true;

				// Reset session
				$query['session_key'] = $this->session_key = $login_result->session_key;

				// Write in cache file
				JFile::write($this->session_cache_file, $this->session_key);

				// Debug ------------
				AKHelper::_('system.mark', 'New session_key: ' . $this->session_key, 'WindWalker');
				// ------------------

				// Resend Request
				$result = $this->doExecute($path, $query, $method, $type);
			}
		}

		return $result;
	}

	/**
	 * doExecute
	 *
	 * @param string $path
	 * @param string $query
	 * @param string $method
	 * @param string $type
	 * @param bool   $ignore_cache
	 *
	 * @return bool
	 */
	public function doExecute($path, $query = '', $method = 'get', $type = 'object', $ignore_cache = false)
	{
		if (!$this->isLogin)
		{
			return false;
		}

		// Set URI Path
		$uri = $this->uri;
		$uri->setPath('/' . trim($path, '/'));
		$uri->setQuery(array());

		// Add json format in Debug mode.
		if (AKDEBUG || $this->forceJson)
		{
			$query['format'] = 'json';
		}

		// Set Query
		if ($method == 'post')
		{
			$query = $this->buildAPIQuery($query, false);
		}
		else
		{
			$query = $this->buildAPIQuery($query, false);
			$uri->setQuery($query);
		}

		// Set Cache
		$key = AKHelper::_('system.uuid', (string) $uri, 3);

		if (isset($this->result[$key]) && !$ignore_cache)
		{
			return $this->handleResult($this->result[$key], $type);
		}

		// Send Request By CURL
		$result = AKHelper::_('curl.getPage', (string) $uri, $method, $query);

		// Debug ------------
		$this->i++;
		$query = $this->buildAPIQuery($query, true);
		$query = $query ? '?' . $query : '';
		$query = $method == 'post' ? $query : '';
		AKHelper::_('system.mark', "Send {$this->i} ({$method}): " . (string) $uri, 'WindWalker');
		// ------------------

		if (!$result)
		{
			$this->setError(AKHelper::_('curl.getError'));

			return false;
		}

		$this->result[$key] = $result;

		return $this->handleResult($this->result[$key], $type);
	}

	/**
	 * handleResult
	 *
	 * @param mixed  $data
	 * @param string $type
	 *
	 * @return bool
	 */
	public function handleResult($data, $type = 'object')
	{
		$result = json_decode($data);

		// Is json format?
		if ($result === null)
		{
			$this->setError('Return string not JSON format.');

			return false;
		}

		// Detect Error message
		if (!isset($result->ApiResult))
		{
			if (isset($result->ApiError->errorMsg))
			{
				$this->setError($result->ApiError->errorMsg);

				// If 403, need relogin.
				if ($result->ApiError->errorNum == 403)
				{
					if ($this->relogin)
					{
						$this->isLogin = false;
					}

					$this->relogin = true;
				}

				return false;
			}
			else
			{
				$this->setError('API System return no result.');

				return false;
			}
		}

		// Set return type.
		if ($type == 'array')
		{
			$result = json_decode($data, true);

			return $result['ApiResult'];
		}
		elseif ($type == 'raw')
		{
			return $data;
		}
		else
		{
			return $result->ApiResult;
		}
	}

	/**
	 * getSessionKey
	 *
	 * @return bool|mixed
	 */
	public function getSessionKey()
	{
		// Read session key from cache file
		$cache_path = $this->session_cache_path;
		$cache_file = $this->session_cache_file;

		if (!JFile::exists($cache_file))
		{
			$content = '';
			JFolder::create($cache_path);
			JFile::write($cache_file, $content);
		}

		$session_key = JFile::read($cache_file);

		// Debug
		AKHelper::_('system.mark', 'session_key: ' . $session_key, 'WindWalker');

		if (!$session_key)
		{
			// Login
			$result = $this->login();

			if (!$result)
			{
				$this->setError('Need login.');
				$this->isLogin = false;

				return false;
			}

			if (!$result->success)
			{
				$this->setError('Need login.');
				$this->isLogin = false;

				return false;
			}

			// Write in cache file
			JFile::write($cache_file, $result->session_key);
			$session_key = $result->session_key;
		}

		return $session_key;
	}

	/**
	 * login
	 *
	 * @return bool
	 */
	public function login()
	{
		$uriQuery['username'] = $this->username;
		$uriQuery['password'] = $this->password;

		// Do execute
		$result = $this->doExecute('/user/login', $uriQuery, 'post');

		return $result;
	}

	/**
	 * getURI
	 *
	 * @return JURI|string
	 */
	public function getURI()
	{
		return $this->uri;
	}

	/**
	 * setVar
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return void
	 */
	public function setVar($name, $value)
	{
		$this->uri->setVar($name, $value);
	}

	/**
	 * getVar
	 *
	 * @param string $name
	 * @param null   $default
	 *
	 * @return void
	 */
	public function getVar($name, $default = null)
	{
		$this->uri->getVar($name, $default);
	}

	/**
	 * delVar
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function delVar($name)
	{
		$this->uri->delVar($name);
	}

	/**
	 * setQuery
	 *
	 * @param $queries
	 *
	 * @return void
	 */
	public function setQuery($queries)
	{
		$this->uri->setQuery($queries);
	}

	/**
	 * hash
	 *
	 * @param $datas
	 *
	 * @return string
	 */
	public static function hash($datas)
	{
		$datas = (array) $datas;
		$datas = implode('', $datas);

		return md5($datas);
	}

	/**
	 * buildAPIQuery
	 *
	 * @param      $array
	 * @param bool $string
	 *
	 * @return string
	 */
	public function buildAPIQuery($array, $string = true)
	{
		// Remove empty values
		foreach ($array as $key => &$val)
		{
			if ($val === '' || JString::substr($key, 0, 1) == '_')
			{
				unset($array[$key]);
			}

			if (is_array($val) || is_object($val))
			{
				$val = (array) $val;

				foreach ($val as $key2 => &$val2)
				{
					if ($val2 === '' || JString::substr($key2, 0, 1) == '_')
					{
						unset($array[$key][$key2]);
					}
				}
			}
		}

		if ($string)
		{
			$array = http_build_query($array);
		}

		return $array;
	}
}
