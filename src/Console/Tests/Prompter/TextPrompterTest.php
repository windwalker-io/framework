<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests\Prompter;

use Windwalker\Console\Prompter\TextPrompter;

/**
 * Class TextPrompterTest
 *
 * @since  1.0
 */
class TextPrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new TextPrompter('Tell me something: ', null, null, $this->output);
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
		$this->setStream("y");

		$in = $this->instance->ask();

		$this->assertEquals(
			trim($this->output->getOutput()),
			trim('Tell me something: ')
		);

		// Ask by invoke
		$this->setStream("n");

		/** @var $prompter AbstractPrompter */
		$prompter = $this->instance;
		$in = $prompter();

		$this->assertEquals($in, 'n');

		// Set as default in command getArgument
		$command = new \Windwalker\Console\Command\Command('test', $prompter->getInput(), $this->output);

		$this->setStream("fly");

		$this->output->setOutput('');

		$in = $command->getArgument(9, $this->instance);

		$this->assertEquals(
			trim($this->output->getOutput()),
			trim('Tell me something: ')
		);

		$this->assertEquals($in, 'fly');
	}
}
