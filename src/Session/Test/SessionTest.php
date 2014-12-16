<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Test;

use Windwalker\Session\Bag\ArrayBag;
use Windwalker\Session\Session;
use Windwalker\Session\Test\Mock\MockArrayBridge;

/**
 * Test class of Session
 *
 * @since 2.0
 */
class SessionTest extends AbstractSessionTestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->bridge = new MockArrayBridge('PHPSESSID');

		$this->bag = new ArrayBag;

		$this->options = array(
			'expire_time' => 20,
			'force_ssl' => true,
			'security' => 'security'
		);

		parent::setUp();

		$this->instance->start();

		$this->instance->set('sakura', 'samuari');
		$this->instance->set('olive', 'peace');
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
	 * Method to test start().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::start
	 */
	public function testStart()
	{
		$keys = array(
			'session.counter',
			'session.timer.start',
			'session.timer.last',
			'session.timer.now',
			'sakura',
			'olive'
		);

		$this->assertEquals($keys, array_keys($this->instance->getAll()));
		$this->assertEquals(array(), $this->instance->getAll('flash'));
	}

	/**
	 * Method to test destroy().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::destroy
	 */
	public function testDestroy()
	{
		$this->instance->set('foo', 'bar');

		$this->instance->destroy();

		$this->instance->start();

		$this->assertNull($this->instance->get('foo'));
	}

	/**
	 * Method to test restart().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::restart
	 */
	public function testRestart()
	{
		$this->instance->set('foo', 'bar');

		$this->instance->restart();

		$this->assertNull($this->instance->get('foo'));
	}

	/**
	 * Method to test fork().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::fork
	 */
	public function testFork()
	{
		$this->instance->fork();

		$this->assertEquals('samuari', $this->instance->get('sakura'));
	}

	/**
	 * Method to test close().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::close
	 */
	public function testClose()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::get
	 */
	public function testGet()
	{
		$this->assertEquals('samuari', $this->instance->get('sakura'));
	}

	/**
	 * Method to test getAll().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getAll
	 */
	public function testGetAll()
	{
		$keys = array(
			'session.counter',
			'session.timer.start',
			'session.timer.last',
			'session.timer.now',
			'sakura',
			'olive'
		);

		$this->assertEquals($keys, array_keys($this->instance->getAll()));
	}

	/**
	 * Method to test set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::set
	 */
	public function testSet()
	{
		$this->instance->set('foo', 'bar');

		$this->assertEquals('bar', $this->instance->get('foo'));
	}

	/**
	 * Method to test has().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::has
	 */
	public function testHas()
	{
		$this->assertTrue($this->instance->has('sakura'));
		$this->assertFalse($this->instance->has('sunflower'));
	}

	/**
	 * Method to test clear().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::clear
	 */
	public function testClear()
	{
		$this->instance->clear('sakura');

		$this->assertFalse($this->instance->has('sakura'));
	}

	/**
	 * Method to test addFlash().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::addFlash
	 */
	public function testAddAndTakeFlash()
	{
		$this->instance->addFlash('Yoo', 'warning');

		$flashes = $this->instance->getFlashes();

		$this->assertEquals(array('warning' => array('Yoo')), $flashes);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getIterator
	 */
	public function testGetIterator()
	{
		$keys = array(
			'session.counter',
			'session.timer.start',
			'session.timer.last',
			'session.timer.now',
			'sakura',
			'olive'
		);

		$this->assertEquals($keys, array_keys(iterator_to_array($this->instance->getIterator())));
	}

	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getName
	 */
	public function testGetName()
	{
		$this->assertEquals($this->name, $this->instance->getName());
	}

	/**
	 * Method to test getId().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getId
	 */
	public function testGetId()
	{
		$this->assertEquals($this->id, $this->instance->getId());
	}

	/**
	 * Method to test isActive().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::isActive
	 */
	public function testIsActive()
	{
		$this->assertTrue($this->instance->isActive());

		$this->instance->destroy();

		$this->assertFalse($this->instance->isActive());
	}

	/**
	 * Method to test isNew().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::isNew
	 */
	public function testIsNew()
	{
		$this->assertTrue($this->instance->isNew());

		$this->instance->set('session.counter', 2);

		$this->assertFalse($this->instance->isNew());
	}

	/**
	 * Method to test getState().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getState
	 */
	public function testGetState()
	{
		$this->assertEquals(Session::STATE_ACTIVE, $this->instance->getState());

		$this->instance->destroy();

		$this->assertEquals(Session::STATE_DESTROYED, $this->instance->getState());

		$this->instance->setOption('expire_time', -1);

		$this->instance->start();

		$this->assertEquals(Session::STATE_EXPIRED, $this->instance->getState());
	}

	/**
	 * Method to test setState().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setState
	 * @TODO   Implement testSetState().
	 */
	public function testSetState()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCookie().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getCookie
	 */
	public function testGetCookie()
	{
		$_COOKIE['a'] = 'b';

		$this->assertEquals($_COOKIE, $this->instance->getCookie());
	}

	/**
	 * Method to test setCookie().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setCookie
	 * @TODO   Implement testSetCookie().
	 */
	public function testSetCookie()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getOption().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getOption
	 */
	public function testGetOption()
	{
		$this->assertEquals(20, $this->instance->getOption('expire_time'));

		$this->assertEquals('default', $this->instance->getOption('foo', 'default'));
	}

	/**
	 * Method to test setOption().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setOption
	 * @TODO   Implement testSetOption().
	 */
	public function testSetOption()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getOptions().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getOptions
	 * @TODO   Implement testGetOptions().
	 */
	public function testGetOptions()
	{
		$this->assertEquals($this->options, $this->instance->getOptions());
	}

	/**
	 * Method to test setOptions().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setOptions
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
	 * Method to test getBags().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getBags
	 */
	public function testGetBags()
	{
		$bags = $this->instance->getBags();

		$this->assertInstanceOf('Windwalker\Session\Bag\ArrayBag', $bags['default']);
		$this->assertInstanceOf('Windwalker\Session\Bag\FlashBag', $bags['flash']);
	}

	/**
	 * Method to test setBags().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setBags
	 * @TODO   Implement testSetBags().
	 */
	public function testSetBags()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getBag().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getBag
	 * @TODO   Implement testGetBag().
	 */
	public function testGetBag()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setBag().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setBag
	 * @TODO   Implement testSetBag().
	 */
	public function testSetBag()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFlashBag().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::getFlashBag
	 * @TODO   Implement testGetFlashBag().
	 */
	public function testGetFlashBag()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setFlashBag().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setFlashBag
	 * @TODO   Implement testSetFlashBag().
	 */
	public function testSetFlashBag()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDebug().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Session\Session::setDebug
	 * @TODO   Implement testSetDebug().
	 */
	public function testSetDebug()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
