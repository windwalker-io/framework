<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests\Prompter;

use Windwalker\Console\Prompter\SelectPrompter;

/**
 * Class SelectPrompterTest
 *
 * @since  1.0
 */
class SelectPrompterTest extends AbstractPrompterTest
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array('red', 'yellow', 'blue');

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

		$this->instance = $prompter = new SelectPrompter(null, null, $this->options, null, $this->output);
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
		// Invalidate input test
		$this->setStream("4\n5\n6\n7\n8");

		$in = $this->instance->setAttempt(5)
			->ask('Please select an option []:', 2);

		$outputCompare = <<<EOF
  [0] - red
  [1] - yellow
  [2] - blue

Please select an option []:
  Not a valid selection

Please select an option []:
  Not a valid selection

Please select an option []:
  Not a valid selection

Please select an option []:
  Not a valid selection

Please select an option []:
  Not a valid selection
EOF;

		$this->assertEquals(
			str_replace(PHP_EOL, "\n", trim($outputCompare)),
			str_replace(PHP_EOL, "\n", trim($this->output->getOutput()))
		);

		// Default value
		$this->assertEquals($in, 2, 'Return value should be default (2).');

		$this->setStream("1");

		$in = $this->instance->ask('Please select an option []:', 2);

		$this->assertEquals($in, 1);
	}
}
