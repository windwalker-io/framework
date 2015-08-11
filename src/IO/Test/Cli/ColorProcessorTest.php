<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Test\Cli;

use Windwalker\IO\Cli\Color\ColorProcessor;
use Windwalker\IO\Cli\Color\ColorStyle;
use Windwalker\Test\TestEnvironment;

/**
 * Test class of ColorProcessor
 *
 * @since 2.0
 */
class ColorProcessorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var ColorProcessor
	 */
	protected $instance;

	/**
	 * Property winOs.
	 *
	 * @var boolean
	 */
	protected $winOs;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new ColorProcessor;

		$this->winOs = TestEnvironment::isWindows();

		if ($this->winOs)
		{
			$this->instance->setNoColors(true);
		}
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test addStyle().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Cli\Color\ColorProcessor::addStyle
	 */
	public function testAddStyle()
	{
		$style = new ColorStyle('red');
		$this->instance->addStyle('foo', $style);

		$check = ($this->winOs) ? 'foo' : '[31mfoo[0m';

		$this->assertThat(
			$this->instance->process('<foo>foo</foo>'),
			$this->equalTo($check)
		);
	}

	/**
	 * Method to test stripColors().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Cli\Color\ColorProcessor::stripColors
	 */
	public function testStripColors()
	{
		$this->assertThat(
			$this->instance->stripColors('<foo>foo</foo>'),
			$this->equalTo('foo')
		);
	}

	/**
	 * Method to test process().
	 *
	 * @return void
	 *
	 * @covers Windwalker\IO\Cli\Color\ColorProcessor::process
	 */
	public function testProcess()
	{
		$check = ($this->winOs) ? 'foo' : '[31mfoo[0m';

		$this->assertThat(
			$this->instance->process('<fg=red>foo</fg=red>'),
			$this->equalTo($check)
		);
	}

	/**
	 * Tests the process method for replacing colors
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\IO\Cli\Color\ColorProcessor::process
	 * @since   2.0
	 */
	public function testProcessNamed()
	{
		$style = new ColorStyle('red');
		$this->instance->addStyle('foo', $style);

		$check = ($this->winOs) ? 'foo' : '[31mfoo[0m';

		$this->assertThat(
			$this->instance->process('<foo>foo</foo>'),
			$this->equalTo($check)
		);
	}

	/**
	 * Tests the process method for replacing colors
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\IO\Cli\Color\ColorProcessor::replaceColors
	 * @since   2.0
	 */
	public function testProcessReplace()
	{
		$check = ($this->winOs) ? 'foo' : '[31mfoo[0m';

		$this->assertThat(
			$this->instance->process('<fg=red>foo</fg=red>'),
			$this->equalTo($check)
		);
	}
}
