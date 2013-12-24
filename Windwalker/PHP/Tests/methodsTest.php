<?php
/**
 * @copyright  Copyright (C) 2013 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\PHP\Tests;

/**
 * Tests for the global PHP methods.
 *
 * @since  1.0
 */
class methodsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests the with method.
	 *
	 * @return  void
	 *
	 * @since   1.0
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
