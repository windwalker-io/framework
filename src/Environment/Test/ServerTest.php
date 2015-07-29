<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment\Test;

use Windwalker\Environment\Server;

/**
 * Test class of Server
 *
 * @since 2.0
 */
class ServerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Server
	 */
	protected $instance;

	/**
	 * Property os.
	 *
	 * @var boolean
	 */
	protected $os;

	/**
	 * Property isWin.
	 *
	 * @var  boolean
	 */
	protected $isWin;

	/**
	 * Property isMac.
	 *
	 * @var  boolean
	 */
	protected $isMac;

	/**
	 * Property isUnix.
	 *
	 * @var  boolean
	 */
	protected $isUnix;

	/**
	 * Property isLinux.
	 *
	 * @var  boolean
	 */
	protected $isLinux;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new Server;

		// Detect the native operating system type.
		$this->os = strtoupper(substr(PHP_OS, 0, 3));

		$this->isWin = $this->os == 'WIN';

		$this->isMac = $this->os == 'MAC';

		$this->isUnix = in_array($this->os, array('CYG', 'DAR', 'FRE', 'LIN', 'NET', 'OPE', 'MAC'));

		$this->isLinux = $this->os == 'LIN';
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
	 * getOSTestData
	 *
	 * @return  array
	 */
	public function getIsWinTestData()
	{
		return array(
			array('CYGWIN_NT-5.1', false),
			array('Darwin',  false),
			array('FreeBSD', false),
			array('HP-UX',   false),
			array('IRIX64',  false),
			array('Linux',   false),
			array('NetBSD',  false),
			array('OpenBSD', false),
			array('SunOS',   false),
			array('Unix',    false),
			array('WIN32',   true),
			array('WINNT',   true),
			array('Windows', true)
		);
	}

	/**
	 * getOSTestData
	 *
	 * @return  array
	 */
	public function getIsUnixTestData()
	{
		return array(
			array('CYGWIN_NT-5.1', true),
			array('Darwin',  true),
			array('FreeBSD', true),
			array('HP-UX',   true),
			array('IRIX64',  true),
			array('Linux',   true),
			array('NetBSD',  true),
			array('OpenBSD', true),
			array('SunOS',   true),
			array('Unix',    true),
			array('WIN32',   false),
			array('WINNT',   false),
			array('Windows', false)
		);
	}

	/**
	 * getOSTestData
	 *
	 * @return  array
	 */
	public function getIsLinuxTestData()
	{
		return array(
			array('CYGWIN_NT-5.1', false),
			array('Darwin',  false),
			array('FreeBSD', false),
			array('HP-UX',   false),
			array('IRIX64',  false),
			array('Linux',   true),
			array('NetBSD',  false),
			array('OpenBSD', false),
			array('SunOS',   false),
			array('Unix',    false),
			array('WIN32',   false),
			array('WINNT',   false),
			array('Windows', false)
		);
	}

	/**
	 * Method to test getOS().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\Environment\Server::getOS
	 */
	public function testGetOS()
	{
		$this->instance->setUname('Darwin');

		$this->assertEquals('DAR', $this->instance->getOS());
	}

	/**
	 * Method to test isWin().
	 *
	 * @param string  $os
	 * @param boolean $value
	 *
	 * @return void
	 *
	 * @dataProvider getIsWinTestData
	 *
	 * @covers       Windwalker\Application\Environment\Server::isWin
	 */
	public function testIsWin($os, $value)
	{
		$this->instance->setOS(null);
		$this->instance->setUname($os);

		$this->assertEquals($value, $this->instance->isWin());
	}

	/**
	 * Method to test isUnix().
	 *
	 * @param string  $os
	 * @param boolean $value
	 *
	 * @return void
	 *
	 * @dataProvider getIsUnixTestData
	 *
	 * @covers Windwalker\Application\Environment\Server::isUnix
	 */
	public function testIsUnix($os, $value)
	{
		$this->instance->setOS(null);
		$this->instance->setUname($os);

		$this->assertEquals($value, $this->instance->isUnix());
	}

	/**
	 * Method to test isLinux().
	 *
	 * @param string  $os
	 * @param boolean $value
	 *
	 * @return void
	 *
	 * @dataProvider getIsLinuxTestData
	 *
	 * @covers Windwalker\Application\Environment\Server::isLinux
	 */
	public function testIsLinux($os, $value)
	{
		$this->instance->setOS(null);
		$this->instance->setUname($os);

		$this->assertEquals($value, $this->instance->isLinux());
	}
}
