<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Authentication\Test;

use Windwalker\Authentication\Authentication;
use Windwalker\Authentication\Credential;
use Windwalker\Authentication\Method\LocalMethod;
use Windwalker\Authentication\Test\Mock\MockMethod;

/**
 * Test class of Authentication
 *
 * @since 2.0
 */
class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Authentication
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
		$this->instance = new Authentication;
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
	 * Method to test authenticate().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Authentication\Authenticate::authenticate
	 */
	public function testAuthenticate()
	{
		$credential = new Credential(array('username' => 'flower', 'password' => '1234'));

		// Test if no method
		$this->assertFalse($this->instance->authenticate($credential));

		// Test for MockMethod
		$this->instance->addMethod('mock', new MockMethod);

		// Test success
		$this->assertTrue($this->instance->authenticate($credential));

		$this->assertEquals('mock', $credential->_authenticated_method);

		$this->assertEquals(array('mock' => Authentication::SUCCESS), $this->instance->getResults());

		// Test invalid
		$credential->password = '4321';

		$this->assertFalse($this->instance->authenticate($credential));

		$this->assertEquals(array('mock' => Authentication::INVALID_CREDENTIAL), $this->instance->getResults());

		// Test No user
		$credential->username = 'sakura';

		$this->assertFalse($this->instance->authenticate($credential));

		$this->assertEquals(array('mock' => Authentication::USER_NOT_FOUND), $this->instance->getResults());
	}

	/**
	 * Method to test addMethod().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Authentication\Authenticate::addMethod
	 */
	public function testAddAndGetMethod()
	{
		$this->instance->addMethod('flower', new MockMethod);

		$this->assertInstanceOf('Windwalker\Authentication\Test\Mock\MockMethod', $this->instance->getMethod('flower'));
	}

	/**
	 * Method to test removeMethod().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Authentication\Authenticate::removeMethod
	 * @TODO   Implement testRemoveMethod().
	 */
	public function testRemoveMethod()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getResults().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Authentication\Authenticate::getResults
	 */
	public function testGetResults()
	{
		$this->instance->addMethod('a', new LocalMethod);
		$this->instance->addMethod('b', new MockMethod);

		$credential = new Credential(array('username' => 'flower', 'password' => '1234'));

		$this->instance->authenticate($credential);

		$this->assertEquals(
			array(
				'a' => Authentication::USER_NOT_FOUND,
				'b' => Authentication::SUCCESS
			),
			$this->instance->getResults()
		);
	}
}
