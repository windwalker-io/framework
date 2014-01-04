<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class CallableHelper
 *
 * @since 1.0
 */
class CallableHelper
{
	/**
	 * getArgumentFromData
	 *
	 * @param mixed $arguments
	 * @param mixed $data
	 *
	 * @return  null
	 */
	public static function getArgumentFromData($arguments, $data)
	{
		if (empty($arguments))
		{
			return null;
		}

		$args = is_array($arguments) ? $arguments : explode('.', $arguments);

		$dataTmp = $data;

		foreach ($args as $arg)
		{
			if (is_object($dataTmp) && !empty($dataTmp->$arg))
			{
				$dataTmp = $dataTmp->$arg;
			}
			elseif (is_array($dataTmp) && !empty($dataTmp[$arg]))
			{
				$dataTmp = $dataTmp[$arg];
			}
			else
			{
				return null;
			}
		}

		return $dataTmp;
	}
}
