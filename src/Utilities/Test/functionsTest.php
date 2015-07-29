<?php
/**
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Source Matters, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Test;

/**
 * Tests for the global PHP methods.
 *
 * @since  2.0
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests the with method.
	 *
	 * @return  void
	 *
	 * @since   2.0
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
