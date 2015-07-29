<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Test\Cli;

use Windwalker\IO\Cli\Color\ColorStyle;

/**
 * Test class of ColorStyle
 *
 * @since 2.0
 */
class ColorStyleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var ColorStyle
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new ColorStyle('red', 'white', array('blink'));
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
	 * Test the GetStyle method.
	 *
	 * @covers Windawlker\IO\Cli\Color\ColorStyle::getStyle
	 *
	 * @return void
	 */
	public function testGetStyle()
	{
		$this->assertThat(
			$this->instance->getStyle(),
			$this->equalTo('31;47;5')
		);
	}

	/**
	 * Test the ToString method.
	 *
	 * @return void
	 */
	public function testToString()
	{
		$this->assertThat(
			$this->instance->__toString(),
			$this->equalTo('31;47;5')
		);
	}

	/**
	 * Test the __construct method.
	 *
	 * @return void
	 */
	public function fromString()
	{
		$style = new ColorStyle('white', 'red', array('blink', 'bold'));

		$this->assertThat(
			$this->instance->fromString('fg=white;bg=red;options=blink,bold'),
			$this->equalTo($style)
		);
	}

	/**
	 * Test the fromString method.
	 *
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testFromStringInvalid()
	{
		$this->instance->fromString('XXX;XX=YY');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid1()
	{
		new ColorStyle('INVALID');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid2()
	{
		new ColorStyle('', 'INVALID');
	}

	/**
	 * Test the __construct method.
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function testConstructInvalid3()
	{
		new ColorStyle('', '', array('INVALID'));
	}
}
