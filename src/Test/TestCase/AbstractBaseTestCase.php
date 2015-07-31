<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Test\Helper\StringHelper;

/**
 * The AbstractBaseTestCase class.
 * 
 * @since  2.0
 */
abstract class AbstractBaseTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * assertStringDataEquals
	 *
	 * @param string $expected
	 * @param string $actual
	 * @param string $message
	 * @param int    $delta
	 * @param int    $maxDepth
	 * @param bool   $canonicalize
	 * @param bool   $ignoreCase
	 *
	 * @return  void
	 */
	public function assertStringDataEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
	{
		$this->assertEquals(
			StringHelper::clean($expected),
			StringHelper::clean($actual),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

	/**
	 * assertStringDataEquals
	 *
	 * @param string $expected
	 * @param string $actual
	 * @param string $message
	 * @param int    $delta
	 * @param int    $maxDepth
	 * @param bool   $canonicalize
	 * @param bool   $ignoreCase
	 *
	 * @return  void
	 */
	public function assertStringSafeEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
	{
		$this->assertEquals(
			trim(StringHelper::removeCRLF($expected)),
			trim(StringHelper::removeCRLF($actual)),
			$message,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

	/**
	 * assertExpectedException
	 *
	 * @param callable $closure
	 * @param string   $class
	 * @param string   $msg
	 * @param int      $code
	 * @param string   $message
	 *
	 * @return  void
	 */
	public function assertExpectedException($closure, $class = 'Exception', $msg = null, $code = null, $message = '')
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}

		try
		{
			$closure();
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf($class, $e, $message);

			if ($msg)
			{
				$this->assertEquals($msg, $e->getMessage(), $message);
			}

			if ($code)
			{
				$this->assertEquals($code, $e->getCode(), $message);
			}

			return;
		}

		$this->fail('No exception caught.');
	}
}
