<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment\Web;

use Windwalker\Environment\Environment;
use Windwalker\Environment\ServerInterface;

/**
 * The WebEnvironment class.
 * 
 * @since  2.0
 */
class WebEnvironment extends Environment
{
	/**
	 * Property client.
	 *
	 * @var  WebClient
	 */
	public $client;

	/**
	 * Class init.
	 *
	 * @param WebClient       $client
	 * @param ServerInterface $server
	 */
	public function __construct(WebClient $client = null, ServerInterface $server = null)
	{
		$this->client = $client ? : new WebClient;

		parent::__construct($server);
	}

	/**
	 * Method to get property Client
	 *
	 * @return  WebClient
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Method to set property client
	 *
	 * @param   WebClient $client
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setClient($client)
	{
		$this->client = $client;

		return $this;
	}
}
