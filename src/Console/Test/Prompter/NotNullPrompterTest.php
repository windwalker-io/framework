<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\NotNullPrompter;

/**
 * Class ValidatePrompterTest
 *
 * @since  2.0
 */
class NotNullPrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new NotNullPrompter('Tell me something: ', null, null, $this->io);

		$this->setStream(" ");
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
		$this->assertEquals($this->instance->ask('Tell me something: ', 'sakura'), 'sakura', 'Should validate fail and return default.');

		$this->setStream('sakura');

		$this->assertEquals($this->instance->ask('Tell me something: '), 'sakura', 'Should validate success and pass.');
	}
}
