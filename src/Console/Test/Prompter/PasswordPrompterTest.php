<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Test\Prompter\Stubs\FakePasswordPrompter;
use Windwalker\Test\TestEnvironment;

/**
 * Class PasswordPrompterTest
 *
 * @since  2.0
 */
class PasswordPrompterTest extends AbstractPrompterTest
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = $prompter = new FakePasswordPrompter(null, null, $this->io);
	}

	/**
	 * Test prompter ask.
	 *
	 * @return  void
	 *
	 * @since  2.0
	 */
	public function testAsk()
	{
		if (TestEnvironment::isWindows())
		{
			$this->markTestSkipped('This test is not supported on Windows');
		}

		$this->markTestSkipped('This test is not supported now');

		return;

		$this->setStream("1234qwer\n");

		$in = $this->instance->ask('Enter password: ');

		$this->assertEquals('1234qwer', $in);
	}
}
