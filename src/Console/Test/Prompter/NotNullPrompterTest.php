<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\NotNullPrompter;

/**
 * Class ValidatePrompterTest
 *
 * @since  1.0
 */
class NotNullPrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new NotNullPrompter('Tell me something: ', null, null, $this->io);

		$this->setStream(" ");
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
		$this->assertEquals($this->instance->ask('Tell me something: ', 'sakura'), 'sakura', 'Should validate fail and return default.');

		$this->setStream('sakura');

		$this->assertEquals($this->instance->ask('Tell me something: '), 'sakura', 'Should validate success and pass.');
	}
}
