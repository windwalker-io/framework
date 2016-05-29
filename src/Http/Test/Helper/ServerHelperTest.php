<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Helper;

use Windwalker\Http\Helper\ServerHelper;
use Windwalker\Http\UploadedFile;

/**
 * Test class of ServerHelper
 *
 * @since {DEPLOY_VERSION}
 */
class ServerHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test getValue().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\ServerHelper::getValue
	 */
	public function testGetValue()
	{
		$servers = array(
			'HTTP_FOO' => 'foo',
			'X_BAR' => 'bar',
			'CONTENT_BAZ' => array('baz'),
		);

		$this->assertEquals(ServerHelper::getValue($servers, 'HTTP_FOO'), 'foo');
		$this->assertEquals(ServerHelper::getValue($servers, 'HTTP_BAR'), null);
		$this->assertEquals(ServerHelper::getValue($servers, 'x_bar'), null);
		$this->assertEquals(ServerHelper::getValue($servers, 'x_bar', 'default'), 'default');
	}

	/**
	 * Method to test validateUploadedFiles().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\ServerHelper::validateUploadedFiles
	 */
	public function testValidateUploadedFiles()
	{
		$files = array(new UploadedFile('php://memory'));

		$this->assertTrue(ServerHelper::validateUploadedFiles($files));

		// Nested array
		$files[] = $files;

		$this->assertTrue(ServerHelper::validateUploadedFiles($files));
	}

	/**
	 * Method to test getAllHeaders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\ServerHelper::getAllHeaders
	 * @TODO   Implement testGetAllHeaders().
	 */
	public function testGetAllHeaders()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test apacheRequestHeaders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Http\Helper\ServerHelper::apacheRequestHeaders
	 * @TODO   Implement testApacheRequestHeaders().
	 */
	public function testApacheRequestHeaders()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
