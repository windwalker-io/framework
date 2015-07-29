<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Application\Web;

use Windwalker\Environment\Environment;
use Windwalker\Environment\ServerInterface;
use Windwalker\Environment\Web\WebClient;

/**
 * The WebEnvironment class.
 *
 * This class based on Windwalker Environment class and improved for web application.
 * 
 * @since  2.0
 */
class WebEnvironment extends Environment
{
	/**
	 * The web client object.
	 *
	 * @var    WebClient
	 * @since  2.0
	 */
	public $client;

	/**
	 * Constructor of this class.
	 *
	 * @param   WebClient        $client  The WebClient object to determine browser version.
	 * @param   ServerInterface  $server  The server information object.
	 *
	 * @since   2.0
	 */
	public function __construct(WebClient $client = null, ServerInterface $server = null)
	{
		$this->client = $client ? : new WebClient;

		parent::__construct($server);
	}

	/**
	 * Method to get Client object.
	 *
	 * @return  WebClient  The web client object.
	 *
	 * @since   2.0
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Method to set property client
	 *
	 * @param   WebClient $client  The web client object.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   2.0
	 */
	public function setClient($client)
	{
		$this->client = $client;

		return $this;
	}
}
