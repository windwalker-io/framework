<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Helper\ServerHelper;
use Windwalker\Http\Output\HttpCompressor;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Uri\PsrUri;
use Windwalker\Uri\UriData;

/**
 * The WebServer class.
 *
 * @property-read  UriData        uri
 * @property-read  HttpCompressor compressor
 *
 * @since  3.0
 */
class WebHttpServer extends HttpServer
{
	const CACHE_ENABLE        = true;
	const CACHE_DISABLE       = false;
	const CACHE_CUSTOM_HEADER = null;

	/**
	 * Property uri.
	 *
	 * @var  PsrUri
	 */
	protected $psrUri;

	/**
	 * Property uriData.
	 *
	 * @var  UriData
	 */
	protected $uriData;

	/**
	 * Property cachable.
	 *
	 * @var  boolean
	 */
	protected $cachable;

	/**
	 * Property mimeType.
	 *
	 * @var  string
	 */
	protected $contentType = 'text/html';

	/**
	 * Property charSet.
	 *
	 * @var  string
	 */
	protected $charSet = 'utf-8';

	/**
	 * Property modifiedDate.
	 *
	 * @var  \DateTime
	 */
	protected $modifiedDate;

	/**
	 * Property compressor.
	 *
	 * @var  HttpCompressor
	 */
	protected $compressor;

	/**
	 * Server constructor.
	 *
	 * @param callable                $handler
	 * @param ServerRequestInterface  $request
	 * @param ResponseInterface       $response
	 * @param OutputInterface         $output
	 */
	public function __construct($handler = null, ServerRequestInterface $request, ResponseInterface $response = null, OutputInterface $output = null)
	{
		parent::__construct($handler, $request, $response, $output);

		$this->uriData = new UriData;

		$this->loadSystemUris();

		$this->compressor = $this->createHttpCompressor();
	}

	/**
	 * listen
	 *
	 * @param callable $errorHandler
	 */
	public function listen($errorHandler = null)
	{
		$response = $this->execute($errorHandler);

		$this->output->respond($response);
	}

	/**
	 * execute
	 *
	 * @param callable $nextHandler
	 *
	 * @return  ResponseInterface
	 */
	public function execute($nextHandler = null)
	{
		if (version_compare(PHP_VERSION, '5.4', '>=') && $this->handler instanceof \Closure)
		{
			$this->handler = $this->handler->bindTo($this);
		}

		$response = call_user_func($this->handler, $this->request, $this->response, $nextHandler);

		if (!$response instanceof ResponseInterface)
		{
			$response = $this->response;
		}

		if (!$response->hasHeader('content-type'))
		{
			$response = $response->withHeader('content-type', $this->getContentType() . '; charset=' . $this->getCharSet());
		}

		return $this->prepareCache($response);
	}

	/**
	 * prepareCache
	 *
	 * @param ResponseInterface $response
	 *
	 * @return  ResponseInterface
	 */
	public function prepareCache(ResponseInterface $response)
	{
		/** @var MessageInterface|ResponseInterface $response */

		// Force cachable
		if ($this->getCachable() === static::CACHE_ENABLE)
		{
			// Expires.
			$response = $response->withoutHeader('Expires')->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 900) . ' GMT');

			// Last modified.
			if ($this->modifiedDate instanceof \DateTime)
			{
				$this->modifiedDate->setTimezone(new \DateTimeZone('UTC'));

				$response = $response->withoutHeader('Last-Modified')->withHeader('Last-Modified', $this->modifiedDate->format('D, d M Y H:i:s') . ' GMT');
			}
		}
		// Force uncachable
		elseif ($this->getCachable() === static::CACHE_DISABLE)
		{
			// Expires in the past.
			$response = $response->withoutHeader('Expires')->withHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT');

			// Always modified.
			$response = $response->withoutHeader('Last-Modified')->withHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
			$response = $response->withoutHeader('Cache-Control')->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

			// HTTP 1.0
			$response = $response->withHeader('pragma', 'no-cache');

		}

		return $response;
	}

	/**
	 * Method to load the system URI strings for the application.
	 *
	 * @param   string  $requestUri  An optional request URI to use instead of detecting one from the
	 *                               server environment variables.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function loadSystemUris($requestUri = null)
	{
		$server = $this->getRequest()->getServerParams();
		$uri = $this->getSystemUri($requestUri);

		$original = $requestUri ? new PsrUri($requestUri) : $this->getRequest()->getUri();

		// Get the host and path from the URI.
		$host = $uri->withQuery('')->withFragment('')->withPath('')->__toString();
		$path = rtrim($uri->getPath(), '/\\');
		$script = trim(ServerHelper::getValue($server, 'SCRIPT_NAME', ''), '/');

		// Check if the path includes "index.php".
		if ($script && strpos($path, $script) === 0)
		{
			// Remove the index.php portion of the path.
			$path = substr_replace($path, '', strpos($path, $script), strlen($script));
			$path = rtrim($path, '/\\');
		}

		$scriptName = pathinfo($script, PATHINFO_BASENAME);

		// Set the base URI both as just a path and as the full URI.
		$this->uriData->full    = rtrim($original->__toString(), '/');
		$this->uriData->current = rtrim($original->withQuery('')->withFragment('')->__toString(), '/');
		$this->uriData->script  = $scriptName;
		$this->uriData->root    = $host . $path;
		$this->uriData->host    = $host;
		$this->uriData->path    = $path;

		// Set the extended (non-base) part of the request URI as the route.
		$route = substr_replace($this->uriData->current, '', 0, strlen($this->uriData->root));
		$route = ltrim($route, '/');

		// Only variables should be passed by reference so we use two lines.
		$file = explode('/', $script);
		$file = array_pop($file);

		if (substr($route, 0, strlen($file)) == $file)
		{
			$route = trim(substr($route, strlen($file)), '/');
		}

		$this->uriData->route = $route;
	}

	/**
	 * Get system Uri object.
	 *
	 * @param   string  $requestUri  The request uri string.
	 * @param   bool    $refresh     Refresh the uri.
	 *
	 * @return  PsrUri  The system Uri object.
	 *
	 * @since   2.0
	 */
	protected function getSystemUri($requestUri = null, $refresh = false)
	{
		if ($this->psrUri && !$refresh)
		{
			return $this->psrUri;
		}

		$uri = $requestUri ? new PsrUri($requestUri) : $this->getRequest()->getUri();

		$server = $this->getRequest()->getServerParams();

		// If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
		if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($server['REQUEST_URI']))
		{
			// We aren't expecting PATH_INFO within PHP_SELF so this should work.
			$uri = $uri->withPath(rtrim(dirname(ServerHelper::getValue($server, 'PHP_SELF')), '/\\'));
		}
		// Pretty much everything else should be handled with SCRIPT_NAME.
		else
		{
			$uri = $uri->withPath(rtrim(dirname(ServerHelper::getValue($server, 'SCRIPT_NAME')), '/\\'));
		}

		// Clear the unused parts of the requested URI.
		$uri = $uri->withFragment('');

		return $this->psrUri = $uri;
	}

	/**
	 * Method to get property MimeType
	 *
	 * @return  string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * Method to set property mimeType
	 *
	 * @param   string $contentType
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Method to get property CharSet
	 *
	 * @return  string
	 */
	public function getCharSet()
	{
		return $this->charSet;
	}

	/**
	 * Method to set property charSet
	 *
	 * @param   string $charSet
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCharSet($charSet)
	{
		$this->charSet = $charSet;

		return $this;
	}

	/**
	 * Method to get property ModifiedDate
	 *
	 * @return  \DateTime
	 */
	public function getModifiedDate()
	{
		return $this->modifiedDate;
	}

	/**
	 * Method to set property modifiedDate
	 *
	 * @param   \DateTime $modifiedDate
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setModifiedDate(\DateTime $modifiedDate)
	{
		$this->modifiedDate = $modifiedDate;

		return $this;
	}

	/**
	 * Method to get property UriData
	 *
	 * @return  UriData
	 */
	public function getUriData()
	{
		return $this->uriData;
	}

	/**
	 * Method to set property uriData
	 *
	 * @param   array|UriData  $uriData
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setUriData($uriData)
	{
		if (!$uriData instanceof UriData)
		{
			$uriData = new UriData($uriData);
		}

		$this->uriData = $uriData;

		return $this;
	}

	/**
	 * __get
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		if ($name == 'uri')
		{
			return $this->uriData;
		}

		if ($name == 'compressor')
		{
			return $this->compressor;
		}

		throw new \OutOfRangeException('Property: ' . $name . ' not exists.');
	}

	/**
	 * Method to get property Compressor
	 *
	 * @return  HttpCompressor
	 */
	public function getCompressor()
	{
		return $this->compressor;
	}

	/**
	 * Method to set property compressor
	 *
	 * @param   HttpCompressor $compressor
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCompressor(HttpCompressor $compressor)
	{
		$this->compressor = $compressor;

		return $this;
	}

	/**
	 * Method to get property Cachable
	 *
	 * @return  boolean
	 */
	public function getCachable()
	{
		return $this->cachable;
	}

	/**
	 * Method to set property cachable
	 *
	 * @param   boolean $cachable
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function cachable($cachable = self::CACHE_CUSTOM_HEADER)
	{
		$this->cachable = $cachable;

		return $this;
	}

	/**
	 * Create Compressor object.
	 *
	 * @param  string $acceptEncoding  The HTTP_ACCEPT_ENCODING param, the common is "gzip, deflate".
	 *
	 * @return HttpCompressor
	 */
	public function createHttpCompressor($acceptEncoding = null)
	{
		if (!$acceptEncoding)
		{
			$servers = $this->getRequest()->getServerParams();

			$acceptEncoding = isset($servers['HTTP_ACCEPT_ENCODING']) ? $servers['HTTP_ACCEPT_ENCODING'] : '';
		}

		return new HttpCompressor($acceptEncoding);
	}
}
