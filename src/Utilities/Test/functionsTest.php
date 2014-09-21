<?php
/**
 * @copyright  Copyright (C) 2013 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Utilities\Test;

/**
 * Tests for the global PHP methods.
 *
 * @since  {DEPLOY_VERSION}
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests the with method.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function testWith()
	{
		$object = with(new \stdClass);

		$this->assertEquals(
			new \stdClass,
			$object
		);
	}
}
