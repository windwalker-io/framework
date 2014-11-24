<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Command\Command;
use Windwalker\Console\Prompter\AbstractPrompter;
use Windwalker\Console\Prompter\TextPrompter;

/**
 * Class TextPrompterTest
 *
 * @since  {DEPLOY_VERSION}
 */
class TextPrompterTest extends AbstractPrompterTest
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

		$this->instance = $prompter = new TextPrompter('Tell me something: ', null, $this->io);
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

		$this->instance->ask();

		$this->assertEquals(
			trim($this->io->getTestOutput()),
			trim('Tell me something: ')
		);

		// Ask by invoke
		$this->setStream("n");

		/** @var $prompter AbstractPrompter */
		$prompter = $this->instance;
		$in = $prompter();

		$this->assertEquals($in, 'n');

		// Set as default in command getArgument
		$command = new Command('test', $prompter->getIO());

		$this->setStream("fly");

		$this->io->setTestOutput('');

		$in = $command->getArgument(9, $this->instance);

		$this->assertEquals(
			trim($this->io->getTestOutput()),
			trim('Tell me something: ')
		);

		$this->assertEquals($in, 'fly');
	}
}
