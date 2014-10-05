<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application;

use Windwalker\Environment\Web\WebClient;
use Windwalker\Environment\Web\WebEnvironment;
use Windwalker\IO\Input;
use Windwalker\Uri\Uri;
use Windwalker\Application\Helper\ApplicationHelper;
use Windwalker\Application\Web\Response;
use Windwalker\Application\Web\ResponseInterface;
use Windwalker\Registry\Registry;

/**
 * Class AbstractWebApplication
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractWebApplication extends AbstractApplication
{
	/**
	 * The application client object.
	 *
	 * @var    WebEnvironment
	 * @since  {DEPLOY_VERSION}
	 */
	public $environment;

	/**
	 * The application response object.
	 *
	 * @var    object
	 * @since  {DEPLOY_VERSION}
	 */
	public $response;

	/**
	 * Property uri.
	 *
	 * @var Uri
	 */
	protected $uri = null;

	/**
	 * Class constructor.
	 *
	 * @param   Input              $input        An optional argument to provide dependency injection for the application's
	 *                                           input object.  If the argument is a Input object that object will become
	 *                                           the application's input object, otherwise a default input object is created.
	 * @param   Registry           $config       An optional argument to provide dependency injection for the application's
	 *                                           config object.  If the argument is a Registry object that object will become
	 *                                           the application's config object, otherwise a default config object is created.
	 * @param   WebEnvironment     $environment  An optional argument to provide dependency injection for the application's
	 *                                           client object.  If the argument is a Web\WebEnvironment object that object will become
	 *                                           the application's client object, otherwise a default client object is created.
	 * @param   ResponseInterface  $response     The response object.
	 */
	public function __construct(Input $input = null, Registry $config = null, WebEnvironment $environment = null, ResponseInterface $response = null)
	{
		$this->environment = $environment instanceof WebEnvironment    ? $environment : new WebEnvironment;
		$this->response    = $response    instanceof ResponseInterface ? $response    : new Response;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input, $config);

		// Set the system URIs.
		$this->loadSystemUris();

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function execute()
	{
		// @event onBeforeExecute

		// Perform application routines.
		$this->doExecute();

		// @event onAfterExecute

		// @event onBeforeRespond

		// Send the application response.
		$this->respond();

		// @event onAfterRespond
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @param   boolean $returnBody
	 *
	 * @return  string
	 */
	public function respond($returnBody = false)
	{
		// If gzip compression is enabled in configuration and the server is compliant, compress the output.
		if ($this->get('gzip') && !ini_get('zlib.output_compression') && (ini_get('output_handler') != 'ob_gzhandler'))
		{
			$this->response->compress($this->environment->client->getEncodings());
		}

		return $this->response->respond($returnBody);
	}

	/**
	 * __toString
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->respond(true);
	}

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string   $url    The URL to redirect to. Can only be http/https URL
	 * @param   boolean  $moved  True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function redirect($url, $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index\.php#', $url))
		{
			$url = $this->get('uri.base.full') . $url;
		}

		// Perform a basic sanity check to make sure we don't have any CRLF garbage.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		/*
		 * Here we need to check and see if the URL is relative or absolute.  Essentially, do we need to
		 * prepend the URL with our base URL for a proper redirect.  The rudimentary way we are looking
		 * at this is to simply check whether or not the URL string has a valid scheme or not.
		 */
		if (!preg_match('#^[a-z]+\://#i', $url))
		{
			// Get a URI instance for the requested URI.
			$uri = new Uri($this->get('uri.current'));

			// Get a base URL to prepend from the requested URI.
			$prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			// We just need the prefix since we have a path relative to the root.
			if ($url[0] == '/')
			{
				$url = $prefix . $url;
			}
			else
				// It's relative to where we are now, so lets add that.
			{
				$parts = explode('/', $uri->toString(array('path')));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url = $prefix . $path . $url;
			}
		}

		// If the headers have already been sent we need to send the redirect statement via JavaScript.
		if ($this->response->checkHeadersSent())
		{
			echo "<script>document.location.href='$url';</script>\n";
		}
		else
		{
			// We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
			if (($this->environment->client->getEngine() == WebClient::TRIDENT) && !ApplicationHelper::isAscii($url))
			{
				$html = '<html><head>';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->response->getCharSet() . '" />';
				$html .= '<script>document.location.href=\'' . $url . '\';</script>';
				$html .= '</head><body></body></html>';

				echo $html;
			}
			else
			{
				// All other cases use the more efficient HTTP header for redirection.
				$this->response->header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
				$this->response->header('Location: ' . $url);
				$this->response->header('Content-Type: text/html; charset=' . $this->response->getCharSet());
			}
		}

		// Close the application after the redirect.
		$this->close();
	}

	/**
	 * Method to set a response header.  If the replace flag is set then all headers
	 * with the given name will be replaced by the new one.  The headers are stored
	 * in an internal array to be sent when the site is sent to the browser.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  Response  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$this->response->setHeader($name, $value, $replace);

		return $this;
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  AbstractWebApplication  Instance of $this to allow chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setBody($content)
	{
		$this->response->setBody($content);

		return $this;
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed  The response body either as an array or concatenated string.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getBody($asArray = false)
	{
		return $this->response->getBody($asArray);
	}

	/**
	 * getResponse
	 *
	 * @return  object
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * setResponse
	 *
	 * @param   object $response
	 *
	 * @return  AbstractWebApplication  Return self to support chaining.
	 */
	public function setResponse($response)
	{
		$this->response = $response;

		return $this;
	}

	/**
	 * Method to load the system URI strings for the application.
	 *
	 * @param   string  $requestUri  An optional request URI to use instead of detecting one from the
	 *                               server environment variables.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function loadSystemUris($requestUri = null)
	{
		if ($this->get('site_uri'))
		{
			$uri = new Uri($this->get('site_uri'));
		}
		else
		{
			$uri = $this->getSystemUri($requestUri);
		}

		$this->set('uri.current', $uri->getOriginal());

		// Get the host and path from the URI.
		$host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$path = rtrim($uri->toString(array('path')), '/\\');
		$script = trim($_SERVER['SCRIPT_NAME'], '/');

		// Check if the path includes "index.php".
		if (strpos($path, $script) === 0)
		{
			// Remove the index.php portion of the path.
			$path = substr_replace($path, '', strpos($path, $script), strlen($script));
			$path = rtrim($path, '/\\');
		}

		// Set the base URI both as just a path and as the full URI.
		$this->set('uri.base.full', $host . $path . '/');
		$this->set('uri.base.host', $host);
		$this->set('uri.base.path', $path . '/');

		// Set the extended (non-base) part of the request URI as the route.
		$route = substr_replace($this->get('uri.current'), '', 0, strlen($this->get('uri.base.full')));

		// Only variables should be passed by reference so we use two lines.
		$file = explode('/', $script);
		$file = array_pop($file);

		if (substr($route, 0, strlen($file)) == $file)
		{
			$route = trim(substr($route, strlen($file)), '/');
		}

		$this->set('uri.route', $route);

		// Get an explicitly set media URI is present.
		$mediaURI = trim($this->get('media_uri'));

		if ($mediaURI)
		{
			if (strpos($mediaURI, '://') !== false)
			{
				$this->set('uri.media.full', $mediaURI);
				$this->set('uri.media.path', $mediaURI);
			}
			else
			{
				// Normalise slashes.
				$mediaURI = trim($mediaURI, '/\\');
				$mediaURI = !empty($mediaURI) ? '/' . $mediaURI . '/' : '/';
				$this->set('uri.media.full', $this->get('uri.base.host') . $mediaURI);
				$this->set('uri.media.path', $mediaURI);
			}
		}
		else
			// No explicit media URI was set, build it dynamically from the base uri.
		{
			$this->set('uri.media.full', $this->get('uri.base.full') . 'media/');
			$this->set('uri.media.path', $this->get('uri.base.path') . 'media/');
		}
	}

	/**
	 * getSystemUri
	 *
	 * @param string $requestUri
	 * @param bool   $refresh
	 *
	 * @return  Uri
	 */
	protected function getSystemUri($requestUri = null, $refresh = false)
	{
		if ($this->uri && !$refresh)
		{
			return $this->uri;
		}

		$requestUri = $requestUri ? : $this->detectRequestUri();

		// Start with the requested URI.
		$uri = new Uri($requestUri);

		// If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
		if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
		{
			// We aren't expecting PATH_INFO within PHP_SELF so this should work.
			$uri->setPath(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));
		}
		else
		// Pretty much everything else should be handled with SCRIPT_NAME.
		{
			$uri->setPath(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
		}

		// Clear the unused parts of the requested URI.
		$uri->setQuery(null);
		$uri->setFragment(null);

		return $this->uri = $uri;
	}

	/**
	 * Method to detect the requested URI from server environment variables.
	 *
	 * @return  string  The requested URI
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function detectRequestUri()
	{
		// First we need to detect the URI scheme.
		if ($this->environment->client->isSSLConnection())
		{
			$scheme = 'https://';
		}
		else
		{
			$scheme = 'http://';
		}

		/*
		 * There are some differences in the way that Apache and IIS populate server environment variables.  To
		 * properly detect the requested URI we need to adjust our algorithm based on whether or not we are getting
		 * information from Apache or IIS.
		 */

		// If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
		if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
		{
			// The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		else
			// If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
		{
			// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
			$uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

			// If the QUERY_STRING variable exists append it to the URI string.
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
			{
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		return trim($uri);
	}

	/**
	 * Method to get property Environment
	 *
	 * @return  \Windwalker\Environment\Web\WebEnvironment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Method to set property environment
	 *
	 * @param   \Windwalker\Environment\Web\WebEnvironment $environment
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setEnvironment($environment)
	{
		$this->environment = $environment;

		return $this;
	}
}
