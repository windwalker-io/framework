<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application;

use Windwalker\Application\Response\ResponseInterface;
use Windwalker\Application\Web\WebClientInterface;
use Windwalker\Uri\Uri;
use Windwalker\Application\Helper\ApplicationHelper;
use Windwalker\Application\Response\Response;
use Windwalker\Application\Web\WebClient;
use Windwalker\Input\Input;
use Windwalker\Registry\Registry;

/**
 * Class AbstractWebApplication
 *
 * @since 1.0
 */
abstract class AbstractWebApplication extends AbstractApplication
{
	/**
	 * The application client object.
	 *
	 * @var    Web\WebClient
	 * @since  1.0
	 */
	public $client;

	/**
	 * The application response object.
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $response;

	/**
	 * Class constructor.
	 *
	 * @param   Input                $input    An optional argument to provide dependency injection for the application's
	 *                                         input object.  If the argument is a Input object that object will become
	 *                                         the application's input object, otherwise a default input object is created.
	 * @param   Registry             $config   An optional argument to provide dependency injection for the application's
	 *                                         config object.  If the argument is a Registry object that object will become
	 *                                         the application's config object, otherwise a default config object is created.
	 * @param   WebClientInterface   $client   An optional argument to provide dependency injection for the application's
	 *                                         client object.  If the argument is a Web\WebClient object that object will become
	 *                                         the application's client object, otherwise a default client object is created.
	 * @param   ResponseInterface    $response The response object.
	 */
	public function __construct(Input $input = null, Registry $config = null, WebClientInterface $client = null, ResponseInterface $response = null)
	{
		$this->client   = $client   instanceof WebClientInterface ? $client   : new WebClient;
		$this->response = $response instanceof ResponseInterface  ? $response : new Response;

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
	 * @since   1.0
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
			$this->response->compress($this->client->getEncodings());
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
	 * @since   1.0
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
			$uri = new Uri($this->get('uri.request'));

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
			if (($this->client->getEngine() == Web\WebClient::TRIDENT) && !ApplicationHelper::isAscii($url))
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
	 * @since   1.0
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$this->response->setHeader($name, $value, $replace);

		return $this;
	}

	/**
	 * Send the response headers.
	 *
	 * @return  AbstractWebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function sendHeaders()
	{
		$this->response->sendHeaders();

		return $this;
	}

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  AbstractWebApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function getBody($asArray = false)
	{
		return $this->getBody($asArray);
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
	 * @since   1.0
	 */
	protected function loadSystemUris($requestUri = null)
	{
		if ($this->get('site_uri'))
		{
			$uri = new Uri($this->get('site_uri'));
		}
		else
		{
			$uri = $this->client->getSystemUri($requestUri);
		}

		$this->set('uri.request', $uri->getOriginal());

		// Get the host and path from the URI.
		$host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$path = rtrim($uri->toString(array('path')), '/\\');

		// Check if the path includes "index.php".
		if (strpos($path, 'index.php') !== false)
		{
			// Remove the index.php portion of the path.
			$path = substr_replace($path, '', strpos($path, 'index.php'), 9);
			$path = rtrim($path, '/\\');
		}

		// Set the base URI both as just a path and as the full URI.
		$this->set('uri.base.full', $host . $path . '/');
		$this->set('uri.base.host', $host);
		$this->set('uri.base.path', $path . '/');

		// Set the extended (non-base) part of the request URI as the route.
		$this->set('uri.route', substr_replace($this->get('uri.request'), '', 0, strlen($this->get('uri.base.full'))));

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
}
 