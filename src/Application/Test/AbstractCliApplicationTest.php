<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Test\Stub\StubCli;
use Windwalker\Test\TestHelper;

/**
 * Test class of AbstractCliApplication
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractCliApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubCli
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new StubCli;
	}

	/**
	 * test__construct
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'Windwalker\\IO\\Cli\\IO',
			$this->instance->io,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'Windwalker\\Registry\\Registry',
			TestHelper::getValue($this->instance, 'config'),
			'Config property wrong type'
		);
	}
}
