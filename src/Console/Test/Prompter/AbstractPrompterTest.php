<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\AbstractPrompter;
use Windwalker\Console\Test\Mock\MockIO;

/**
 * Class AbstractPrompterTest
 *
 * @since 1.0
 */
abstract class AbstractPrompterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var AbstractPrompter
	 */
	protected $instance;

	/**
	 * Property memory.
	 *
	 * @var  resource
	 */
	protected $memory = STDIN;

	/**
	 * Property output.
	 *
	 * @var MockIO
	 */
	protected $io;

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
		$this->io = new MockIO;
	}

	/**
	 * Write in Memory for test.
	 *
	 * @param string $text
	 *
	 * @return  $this
	 */
	protected function setStream($text)
	{
		$this->memory = fopen('php://memory', 'r+', false);
		fputs($this->memory, $text);
		rewind($this->memory);

		$this->instance->getIO()->setInputStream($this->memory);

		return $this->memory;
	}
}

