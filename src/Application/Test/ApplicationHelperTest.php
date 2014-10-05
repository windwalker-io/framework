<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Helper\ApplicationHelper;

/**
 * Test class of ApplicationHelper
 *
 * @since {DEPLOY_VERSION}
 */
class ApplicationHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test isAscii().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\Helper\ApplicationHelper::isAscii
	 */
	public function testIsAscii()
	{
		$this->assertTrue(ApplicationHelper::isAscii('Shakespeare'));
		$this->assertFalse(ApplicationHelper::isAscii('莎士比亞 Shakespeare'));
	}
}
