<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\View\Test;

use Windwalker\Renderer\PhpRenderer;
use Windwalker\View\HtmlView;

/**
 * Test class of HtmlView
 *
 * @since {DEPLOY_VERSION}
 */
class HtmlViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var HtmlView
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
		$paths = new \SplPriorityQueue;

		$paths->insert(__DIR__ . '/Tmpl', 128);

		$this->instance = new HtmlView(array('flower' => 'Sakura'), new PhpRenderer($paths));
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
	 * Method to test getData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\HtmlView::getData
	 */
	public function testGetData()
	{
		$this->assertInstanceOf('Windwalker\Data\Data', $this->instance->getData());
	}

	/**
	 * Method to test setData().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\HtmlView::setData
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
	 * Method to test render().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\HtmlView::render
	 */
	public function testRender()
	{
		$this->assertEquals('<h1>Sakura</h1>', trim($this->instance->setLayout('flower')->render()));
	}
}
