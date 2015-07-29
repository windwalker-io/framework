<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\ValidatePrompter;

/**
 * Class ValidatePrompterTest
 *
 * @since  2.0
 */
class ValidatePrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new ValidatePrompter('Tell me something: ', array('flower', 'sakura', 'rose'), null, $this->io);
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
		$this->setStream("4\n5\n6");

		$this->assertEquals($this->instance->ask(null, 'sakura'), 'sakura', 'Should validate fail and return default.');

		$this->setStream("4\n5\n6");

		$this->assertNull($this->instance->ask(null), 'Should validate fail and get NULL.');

		$this->setStream('sakura');

		$this->assertEquals($this->instance->ask('Tell me something: '), 'sakura', 'Should validate success and pass.');
	}
}
