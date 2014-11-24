<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Bag\ArrayBag;
use Windwalker\Session\Test\AbstractSessionTestCase;
use Windwalker\Session\Test\Mock\MockArrayBridge;

/**
 * The AbstractSessionHandlerTestCase class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class AbstractSessionHandlerTestCase extends AbstractSessionTestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->bridge = new MockArrayBridge('PHPSESSID');

		$this->bag = new ArrayBag;

		$this->options = array(
			'expire_time' => 20,
			'force_ssl' => true,
			'security' => 'security'
		);

		parent::setUp();

		$this->instance->start();

		$this->instance->set('sakura', 'samuari');
		$this->instance->set('olive', 'peace');
	}
}
