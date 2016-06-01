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
use Windwalker\Environment\Browser\Browser;

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
	 * @var    Browser
	 * @since  2.0
	 */
	public $client;

	/**
	 * Constructor of this class.
	 *
	 * @param   Browser         $browser  The WebClient object to determine browser version.
	 * @param   ServerInterface $platform The server information object.
	 *
	 * @since   2.0
	 */
	public function __construct(Browser $browser = null, ServerInterface $platform = null)
	{
		$this->client = $browser ? : new Browser;

		parent::__construct($platform);
	}

	/**
	 * Method to get Client object.
	 *
	 * @return  Browser  The web client object.
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
	 * @param   Browser $client The web client object.
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
