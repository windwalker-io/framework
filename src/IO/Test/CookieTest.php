<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Test;

use Windwalker\IO\Cookie;
use Windwalker\Test\TestHelper;

/**
 * Test class of Cookie
 *
 * @since {DEPLOY_VERSION}
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Cookie
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
		$this->instance = new Cookie;
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
	 * Test the Joomla\IO\Cookie::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\IO\Cookie::__construct
	 * @since   1.1.4
	 */
	public function test__construct()
	{
		// Default constructor call
		$instance = new Cookie;

		$this->assertEquals(
			$_COOKIE,
			TestHelper::getValue($instance, 'data')
		);
	}

	/**
	 * Test the Joomla\IO\Cookie::set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\IO\Cookie::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$instance = new Cookie;
		$instance->set('foo', 'bar');

		$data = TestHelper::getValue($instance, 'data');

		$this->assertArrayHasKey('foo', $data);
		$this->assertContains('bar', $data);
	}
}

// Stub for setcookie
namespace Windwalker\IO;

/**
 * Stub.
 *
 * @param   string  $name      Name
 * @param   string  $value     Value
 * @param   int     $expire    Expire
 * @param   string  $path      Path
 * @param   string  $domain    Domain
 * @param   bool    $secure    Secure
 * @param   bool    $httpOnly  HttpOnly
 *
 * @return  void
 *
 * @since   1.1.4
 */
function setcookie($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
{
	return true;
}
