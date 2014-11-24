<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\View\Test;

use Windwalker\View\SimpleHtmlView;

/**
 * Test class of HtmlView
 *
 * @since {DEPLOY_VERSION}
 */
class SimpleHtmlViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var SimpleHtmlView
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
		$this->instance = new SimpleHtmlView(array('flower' => 'Sakura'));

		$this->instance->setLayout(__DIR__ . '/Tmpl/flower.php');
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
	 * @covers Windwalker\View\HtmlView::escape
	 */
	public function testEscape()
	{
		$this->assertEquals('&lt;dev class=&quot;sakura&quot;&gt;&lt;/dev&gt;', $this->instance->escape('<dev class="sakura"></dev>'));
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
		$this->assertEquals('<h1>Sakura</h1>', trim($this->instance->render()));

		$this->instance->set('flower', 'Olive');

		$this->assertEquals('<h1>Olive</h1>', trim($this->instance->render()));
	}

	/**
	 * Method to test getLayout().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\HtmlView::getLayout
	 * @TODO   Implement testGetLayout().
	 */
	public function testGetLayout()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setLayout().
	 *
	 * @return void
	 *
	 * @covers Windwalker\View\HtmlView::setLayout
	 * @TODO   Implement testSetLayout().
	 */
	public function testSetLayout()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
