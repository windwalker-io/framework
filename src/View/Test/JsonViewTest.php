<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\View\Test;

use Windwalker\Registry\Registry;
use Windwalker\View\JsonView;

/**
 * Test class of JsonView
 *
 * @since 2.0
 */
class JsonViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var JsonView
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
		$this->instance = new JsonView(new Registry);
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
	 * Method to test render().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\JsonView::render
	 */
	public function testRender()
	{
		$this->instance->set('a.b.c', array('foo'));

		$this->assertJsonStringEqualsJsonString('{"a":{"b":{"c":["foo"]}}}', $this->instance->render());

		$view = new JsonView(array('a' => array('b' => array('c' => array('foo')))));

		$this->assertJsonStringEqualsJsonString('{"a":{"b":{"c":["foo"]}}}', $view->render());
	}

	/**
	 * Method to test getOptions().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\JsonView::getOptions
	 * @TODO   Implement testGetOptions().
	 */
	public function testGetOptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setOptions().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\JsonView::setOptions
	 * @TODO   Implement testSetOptions().
	 */
	public function testSetOptions()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDepth().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\JsonView::getDepth
	 * @TODO   Implement testGetDepth().
	 */
	public function testGetDepth()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDepth().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\JsonView::setDepth
	 * @TODO   Implement testSetDepth().
	 */
	public function testSetDepth()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
