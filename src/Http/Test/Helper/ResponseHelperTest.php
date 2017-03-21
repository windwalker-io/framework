<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Helper;

use Windwalker\Http\Helper\ResponseHelper;

/**
 * Test class of ResponseHelper
 *
 * @since 3.0
 */
class ResponseHelperTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Method to test getPhrase().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Helper\ResponseHelper::getPhrase
	 */
	public function testGetPhrase()
	{
		$this->assertEquals('OK', ResponseHelper::getPhrase(200));
		$this->assertEquals('Moved Permanently', ResponseHelper::getPhrase(301));
		$this->assertEquals('Found', ResponseHelper::getPhrase(302));
		$this->assertEquals('Temporary Redirect', ResponseHelper::getPhrase(307));
		$this->assertEquals('Forbidden', ResponseHelper::getPhrase(403));
		$this->assertEquals('Not Found', ResponseHelper::getPhrase(404));
		$this->assertEquals('Internal Server Error', ResponseHelper::getPhrase(500));
	}

	/**
	 * Method to test validateStatus().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Helper\ResponseHelper::validateStatus
	 */
	public function testValidateStatus()
	{
		$this->assertTrue(ResponseHelper::validateStatus(200));
		$this->assertFalse(ResponseHelper::validateStatus(700));
	}
}
