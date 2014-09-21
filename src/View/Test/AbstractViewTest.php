<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Test;

use Windwalker\View\Test\Stub\StubView;

/**
 * Test class of AbstractView
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var StubView
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
		$this->instance = new StubView(array('foo' => 'World'));
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
	 * Method to test escape().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\AbstractView::escape
	 */
	public function testEscape()
	{
		$this->assertEquals('<a class=""></a>', $this->instance->escape('<a class=""></a>'));
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\AbstractView::get
	 */
	public function testGet()
	{
		$this->assertEquals('World', $this->instance->get('foo'));
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\AbstractView::set
	 */
	public function testSet()
	{
		$this->instance->set('flower', 'sakura');

		$this->assertEquals('sakura', $this->instance->get('flower'));
	}

	/**
	 * Method to test getData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\AbstractView::getData
	 */
	public function testGetData()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\AbstractView::setData
	 * @TODO   Implement testSetData().
	 */
	public function testSetData()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * testRender
	 *
	 * @return  void
	 *
	 * @covers Windwalker\View\AbstractView::render
	 */
	public function testRender()
	{
		$this->assertEquals('Hello World!', $this->instance->render());

		$this->instance->set('foo', 'Sakura');

		$this->assertEquals('Hello Sakura!', (string) $this->instance);
	}
}
