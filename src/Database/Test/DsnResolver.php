<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Test;

/**
 * The DsnResolver class.
 * 
 * @since  2.0
 */
abstract class DsnResolver
{
	/**
	 * getDsn
	 *
	 * @param string $driver
	 *
	 * @return  array|bool
	 */
	public static function getDsn($driver)
	{
		$const = 'WINDWALKER_TEST_DB_DSN_' . strtoupper($driver);

		// First let's look to see if we have a DSN defined or in the environment variables.
		if (defined($const) || getenv($const))
		{
			$dsn = defined($const) ? constant($const) : getenv($const);
		}
		else
		{
			return false;
		}

		// Parse DSN to array
		$dsn = str_replace(';', "\n", $dsn);
		$dsn = parse_ini_string($dsn);

		return $dsn;
	}
}
