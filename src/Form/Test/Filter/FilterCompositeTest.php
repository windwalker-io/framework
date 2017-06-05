<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Form\Filter\Test\Filter;

use PHPUnit\Framework\TestCase;
use Windwalker\Form\Filter\FilterComposite;

/**
 * Test class of \Windwalker\Form\Filter\FilterComposite
 *
 * @since 3.2
 */
class FilterCompositeTest extends TestCase
{
	/**
	 * Test instance.
	 *
	 * @var FilterComposite
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
		$this->instance = new FilterComposite;
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
	* Method to test __construct().
	*
	* @return void
	*
	* @covers \Windwalker\Form\Filter\FilterComposite::__construct
	*/
	public function test__construct()
	{
		// TODO: Implement \Windwalker\Form\Filter\FilterComposite::test__construct()
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	* Method to test clean().
	*
	* @return void
	*
	* @covers \Windwalker\Form\Filter\FilterComposite::clean
	*/
	public function testClean()
	{
		$this->instance
			->addFilter(function ($text)
			{
			    return ltrim($text);
			})->addFilter(function ($text)
			{
			    return strtoupper($text);
			});

		self::assertEquals('FOO  ', $this->instance->clean('  foo  '));
	}

	/**
	* Method to test addFilter().
	*
	* @return void
	*
	* @covers \Windwalker\Form\Filter\FilterComposite::addFilter
	*/
	public function testAddFilter()
	{
		// TODO: Implement \Windwalker\Form\Filter\FilterComposite::testAddFilter()
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	* Method to test getFilters().
	*
	* @return void
	*
	* @covers \Windwalker\Form\Filter\FilterComposite::getFilters
	*/
	public function testGetFilters()
	{
		// TODO: Implement \Windwalker\Form\Filter\FilterComposite::testGetFilters()
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	* Method to test setFilters().
	*
	* @return void
	*
	* @covers \Windwalker\Form\Filter\FilterComposite::setFilters
	*/
	public function testSetFilters()
	{
		// TODO: Implement \Windwalker\Form\Filter\FilterComposite::testSetFilters()
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
