<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Test\Prompter\Stubs\FakePasswordPrompter;

/**
 * Class PasswordPrompterTest
 *
 * @since  1.0
 */
class PasswordPrompterTest extends AbstractPrompterTest
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  1.0
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
	 * @since  1.0
	 */
	public function testAsk()
	{
		if (defined('PHP_WINDOWS_VERSION_BUILD'))
		{
			$this->markTestSkipped('This test is not supported on Windows');
		}

		$this->setStream("1234qwer\n");

		$in = $this->instance->ask('Enter password: ');

		$this->assertEquals('1234qwer', $in);
	}
}
