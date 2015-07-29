<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\Compare;

/**
 * Test class of Compare
 *
 * @since 2.0
 */
class CompareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Compare
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
		$this->instance = new Compare('flower', 'sakura', '=');
	}

	/**
	 * Method to test toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::toString
	 */
	public function testToString()
	{
		$this->assertEquals('flower = sakura', $this->instance->toString());

		$this->assertEquals('`flower` = "sakura"', $this->instance->toString('`', '"'));
		$this->assertEquals('{flower} = [sakura]', $this->instance->toString('{}', '[]'));
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::__toString
	 */
	public function test__toString()
	{
		$this->assertEquals('flower = sakura', (string) $this->instance);
	}

	/**
	 * Method to test getCompare2().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::getCompare2
	 */
	public function testGetAndSetCompare2()
	{
		$this->instance->setCompare2('rose');

		$this->assertEquals('rose', $this->instance->getCompare2());
	}

	/**
	 * Method to test getCompare1().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::getCompare1
	 */
	public function testGetAndSetCompare1()
	{
		$this->instance->setCompare2('beautiful');

		$this->assertEquals('beautiful', $this->instance->getCompare2());
	}

	/**
	 * Method to test swap().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::swap
	 */
	public function testSwap()
	{
		$this->assertEquals('sakura = flower', $this->instance->swap()->toString());
	}

	/**
	 * Method to test compare().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::compare
	 */
	public function testCompare()
	{
		$this->assertFalse($this->instance->compare());

		$this->instance->setCompare1('sakura');

		$this->assertTrue($this->instance->compare());

		$compare = new Compare(1, 5, '<=');

		$this->assertTrue($compare->compare());
	}

	/**
	 * Method to test getOperator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::getOperator
	 */
	public function testGetAndSetOperator()
	{
		$this->instance->setOperator('<');

		$this->assertEquals('<', $this->instance->getOperator());
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::quote
	 */
	public function testQuote()
	{
		$this->assertEquals('`foo`', $this->instance->quote('foo', '`'));
		$this->assertEquals('[foo]', $this->instance->quote('foo', '[]'));
	}

	/**
	 * Method to test getHandler().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Compare\Compare::getHandler
	 */
	public function testGetAndSetHandler()
	{
		$this->instance->setHandler(
			function ($compare1, $compare2, $operator)
			{
				return strtoupper($compare1 . ' ' . $operator . ' ' . $compare2);
			}
		);

		$this->assertEquals('FLOWER = SAKURA', $this->instance->toString());

		$self = $this->instance;

		$this->instance->setHandler(
			function ($compare1, $compare2, $operator, $quote1, $quote2) use ($self)
			{
				return strtoupper(
					$self->quote($compare1, $quote1) . ' ' . $operator . ' ' . $self->quote($compare2, $quote2)
				);
			}
		);

		$this->assertEquals('(FLOWER) = {SAKURA}', $this->instance->toString('()', '{}'));

		$this->assertInstanceOf('Closure', $this->instance->getHandler());
	}
}
