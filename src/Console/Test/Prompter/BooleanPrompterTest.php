<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\BooleanPrompter;

/**
 * Class BooleanPrompterTest
 *
 * @since  {DEPLOY_VERSION}
 */
class BooleanPrompterTest extends AbstractPrompterTest
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = $prompter = new BooleanPrompter('True or False [Y/n]: ', null, $this->io);
	}

	/**
	 * Test prompter ask.
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testAsk()
	{
		$this->setStream("y");

		$in = $this->instance->ask();

		$this->assertTrue($in, 'Input result should be TRUE.');


		$this->setStream("yes");

		$in = $this->instance->ask();

		$this->assertTrue($in, 'Input result should be TRUE.');


		$this->setStream("Y");

		$in = $this->instance->ask();

		$this->assertTrue($in, 'Input result should be TRUE.');


		$this->setStream("n");

		$in = $this->instance->ask();

		$this->assertFalse($in, 'Input result should be FALSE.');
	}
}
