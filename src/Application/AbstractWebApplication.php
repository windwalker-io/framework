<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Environment\Browser\Browser;
use Windwalker\Environment\Platform;
use Windwalker\Environment\WebEnvironment;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Response\RedirectResponse;
use Windwalker\Http\WebHttpServer;
use Windwalker\Uri\Uri;
use Windwalker\Application\Helper\ApplicationHelper;
use Windwalker\Registry\Registry;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Windwalker\Uri\UriData;

/**
 * Application for Web HTTP foundation.
 *
 * @property-read  WebEnvironment $environment
 * @property-read  Browser        $browser
 * @property-read  Platform       $platform
 * @property-read  WebHttpServer  $server
 * @property-read  ServerRequest  $request
 * @property-read  UriData        $uri
 *
 * @since 2.0
 */
abstract class AbstractWebApplication extends AbstractApplication
{
	/**
	 * The application environment object.
	 *
	 * @var    WebEnvironment
	 * @since  2.0
	 */
	protected $environment;

	/**
	 * Property browser.
	 *
	 * @var  Browser
	 */
	protected $browser;

	/**
	 * Property platform.
	 *
	 * @var  Platform
	 */
	protected $platform;

	/**
	 * Property request.
	 *
	 * @var  ServerRequestInterface
	 */
	protected $request;

	/**
	 * Property server.
	 *
	 * @var  WebHttpServer
	 */
	protected $server;

	/**
	 * Property uri.
	 *
	 * @var  UriData
	 */
	protected $uri;

	/**
	 * Property finalHandler.
	 *
	 * @var  callable
	 */
	protected $finalHandler;

	/**
	 * Class constructor.
	 *
	 * @param   Request        $request       An optional argument to provide dependency injection for the Http request object.
	 * @param   Registry       $config        An optional argument to provide dependency injection for the application's
	 *                                        config object.
	 * @param   WebEnvironment $environment   An optional argument to provide dependency injection for the application's
	 *                                        environment object.
	 *
	 * @since   2.0
	 */
	public function __construct(ServerRequestInterface $request = null, Registry $config = null, WebEnvironment $environment = null)
	{
		$request     = $request     ? : ServerRequestFactory::createFromGlobals();
		$environment = $environment ? : new WebEnvironment;
		$server      = WebHttpServer::create(array($this, 'dispatch'), $request);

		$this->setEnvironment($environment);
		$this->setServer($server);

		// Call the constructor as late as possible (it runs `init()`).
		parent::__construct($config);

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());
	}

	/**
	 * Execute the application.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function execute()
	{
		$this->prepareExecute();

		// @event onBeforeExecute

		// Perform application routines.
		$response = $this->doExecute();

		// @event onAfterExecute

		$this->postExecute();

		// @event onBeforeRespond

		$this->server->getOutput()->respond($response);

		// @event onAfterRespond
	}

	/**
	 * Method to run the application routines. Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   2.0
	 */
	protected function doExecute()
	{
		return $this->server->execute($this->getFinalHandler());
	}

	/**
	 * Method as the Psr7 WebHttpServer handler.
	 *
	 * @param  Request  $request   The Psr7 ServerRequest to get request params.
	 * @param  Response $response  The Psr7 Response interface to [re[are respond data.
	 * @param  callable $next      The next handler to support middleware pattern.
	 *
	 * @return  Response  The returned response object.
	 *
	 * @since   3.0
	 */
	abstract public function dispatch(Request $request, Response $response, $next = null);

	/**
	 * Magic method to render output.
	 *
	 * @return  string  Rendered string.
	 *
	 * @since   2.0
	 */
	public function __toString()
	{
		ob_start();
		
		$this->execute();
		
		return ob_get_clean();
	}

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string       $url   The URL to redirect to. Can only be http/https URL
	 * @param   boolean|int  $code  True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function redirect($url, $code = 303)
	{
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
			$uri = new Uri($this->server->uri->current);

			// Get a base URL to prepend from the requested URI.
			$prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			// We just need the prefix since we have a path relative to the root.
			if ($url[0] == '/')
			{
				$url = $prefix . $url;
			}
			// It's relative to where we are now, so lets add that.
			else
			{
				$parts = explode('/', $uri->toString(array('path')));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url = $prefix . $path . $url;
			}
		}

		// If the headers have already been sent we need to send the redirect statement via JavaScript.
		if ($this->checkHeadersSent())
		{
			echo "<script>document.location.href='$url';</script>\n";
		}
		else
		{
			// We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
			if (($this->environment->browser->getEngine() == Browser::ENGINE_TRIDENT) && !ApplicationHelper::isAscii($url))
			{
				$html = '<html><head>';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->server->getCharSet() . '" />';
				$html .= '<script>document.location.href=\'' . $url . '\';</script>';
				$html .= '</head><body></body></html>';

				echo $html;
			}
			else
			{
				$this->server->getOutput()->respond(new RedirectResponse($url, $code));
			}
		}

		// Close the application after the redirect.
		$this->close();
	}

	/**
	 * Method to get property Environment
	 *
	 * @return  \Windwalker\Environment\WebEnvironment
	 *
	 * @since   2.0
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Method to set property environment
	 *
	 * @param   \Windwalker\Environment\WebEnvironment $environment
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function setEnvironment(WebEnvironment $environment)
	{
		$this->environment = $environment;

		return $this;
	}

	/**
	 * Method to get property FinalHandler
	 *
	 * @return  callable
	 *
	 * @since   3.0
	 */
	public function getFinalHandler()
	{
		return $this->finalHandler;
	}

	/**
	 * Method to set property finalHandler
	 *
	 * @param   callable $finalHandler
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   3.0
	 */
	public function setFinalHandler($finalHandler)
	{
		$this->finalHandler = $finalHandler;

		return $this;
	}

	/**
	 * Method to check to see if headers have already been sent.
	 * We wrap headers_sent() function with this method for testing reason.
	 *
	 * @return  boolean  True if the headers have already been sent.
	 *
	 * @see     headers_sent()
	 *
	 * @since   3.0
	 */
	public function checkHeadersSent()
	{
		return headers_sent();
	}

	/**
	 * Method to get property Server
	 *
	 * @return  WebHttpServer
	 *
	 * @since   3.0
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Method to set property server
	 *
	 * @param   WebHttpServer $server
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   3.0
	 */
	public function setServer(WebHttpServer $server)
	{
		$this->server = $server;

		return $this;
	}

	/**
	 * Method to get property Request
	 *
	 * @return  Request
	 *
	 * @since   3.0
	 */
	public function getRequest()
	{
		return $this->server->getRequest();
	}

	/**
	 * Method to get property Uri
	 *
	 * @return  UriData
	 *
	 * @since   3.0
	 */
	public function getUri()
	{
		return $this->server->getUriData();
	}

	/**
	 * Method to get property Browser
	 *
	 * @return  Browser
	 *
	 * @since   3.0
	 */
	public function getBrowser()
	{
		return $this->environment->getBrowser();
	}

	/**
	 * Method to get property Platform
	 *
	 * @return  Platform
	 *
	 * @since   3.0
	 */
	public function getPlatform()
	{
		return $this->environment->getPlatform();
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param   $name  string
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		$allowNames = array(
			'environment',
			'server'
		);

		if (in_array($name, $allowNames))
		{
			return $this->$name;
		}

		$getters = array(
			'uri',
			'request',
			'browser',
			'platform'
		);

		if (in_array(strtolower($name), $getters))
		{
			$method = 'get' . ucfirst($name);

			return $this->$method();
		}

		return parent::__get($name);
	}
}
