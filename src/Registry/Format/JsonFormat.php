<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Registry\Format;

use Windwalker\Registry\RegistryHelper;

/**
 * JSON format handler for Registry.
 *
 * @since  2.0
 */
class JsonFormat implements FormatInterface
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object  $struct   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string
	 */
	public static function structToString($struct, array $options = array())
	{
		$depth  = RegistryHelper::getValue($options, 'depth');
		$option = RegistryHelper::getValue($options, 'options', 0);

		if (version_compare(PHP_VERSION, '5.5', '>'))
		{
			$depth = $depth ? : 512;

			return json_encode($struct, $option, $depth);
		}

		/*
		if ($depth)
		{
			throw new \InvalidArgumentException('Depth in json_encode() only support higher than PHP 5.5');
		}
		*/

		return json_encode($struct, $option);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * @param   string  $data     JSON formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 */
	public static function stringToStruct($data, array $options = array())
	{
		$assoc  = RegistryHelper::getValue($options, 'assoc', false);
		$depth  = RegistryHelper::getValue($options, 'depth', 512);
		$option = RegistryHelper::getValue($options, 'options', 0);

		if (PHP_VERSION >= 5.4)
		{
			return json_decode(trim($data), $assoc, $depth, $option);
		}
		else
		{
			return json_decode(trim($data), $assoc, $depth);
		}
	}

	/**
	 * prettyPrint
	 *
	 * @return  bool|int
	 */
	public static function prettyPrint()
	{
		return defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false;
	}
}
