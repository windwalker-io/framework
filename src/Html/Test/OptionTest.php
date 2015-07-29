<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Html\Test;

use Windwalker\Dom\Helper\DomHelper;
use Windwalker\Html\Option;

/**
 * Test class of Option
 *
 * @since 2.0
 */
class OptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Option
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
		$this->instance = new Option('flower', 'sakura', array('class' => 'item'));
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
	 * testToString
	 *
	 * @return  void
	 *
	 * @covers Windwalker\Html\Option::toString
	 *
	 */
	public function testToString()
	{
		$this->assertEquals(
			DomHelper::minify('<option class="item" value="sakura">flower</option>'),
			DomHelper::minify($this->instance)
		);
	}

	/**
	 * Method to test getValue().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Html\Option::getValue
	 */
	public function testGetAndSetValue()
	{
		$this->instance->setValue('sunflower');

		$this->assertEquals('sunflower', $this->instance->getValue());
	}
}
