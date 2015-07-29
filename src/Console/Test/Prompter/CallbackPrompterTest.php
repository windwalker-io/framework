<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\CallbackPrompter;

/**
 * Class CallbackPrompterTest
 *
 * @since  2.0
 */
class CallbackPrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new CallbackPrompter(null, null, $this->io);

		$this->setStream('');
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
		$this->instance->setHandler(
			function($value)
			{
				if ($value == 3)
				{
					return true;
				}

				return false;
			}
		);

		$this->setStream("4\n5\n6");

		$this->assertEquals($this->instance->ask('Tell me something: ', 3), 3, 'Should return 3.');

		$this->setStream("4\n5\n6");

		$this->assertNull($this->instance->ask('Tell me something: '), 'Should not get anything.');

		$this->setStream(3);

		$this->assertEquals($this->instance->ask('Tell me something: '), 3, 'Should return 3.');
	}
}
