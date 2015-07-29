<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment;

/**
 * The Environment class.
 * 
 * @since  2.0
 */
class Environment
{
	/**
	 * Property server.
	 *
	 * @var  ServerInterface
	 */
	public $server;

	/**
	 * Class init.
	 *
	 * @param ServerInterface $server
	 */
	public function __construct(ServerInterface $server = null)
	{
		$this->server = $server ? : new Server;
	}

	/**
	 * Method to get property Server
	 *
	 * @return  \Windwalker\Environment\ServerInterface
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Method to set property server
	 *
	 * @param   \Windwalker\Environment\ServerInterface $server
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setServer($server)
	{
		$this->server = $server;

		return $this;
	}
}
