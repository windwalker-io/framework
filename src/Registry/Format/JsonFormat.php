<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Registry\Format;

use Windwalker\Registry\Helper\RegistryHelper;

/**
 * JSON format handler for Registry.
 *
 * @since  {DEPLOY_VERSION}
 */
class JsonFormat implements FormatInterface
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object $object  Data source object.
	 * @param   array  $options Options used by the formatter.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  string  JSON formatted string.
	 */
	public static function objectToString($object, $options = array())
	{
		$depth  = RegistryHelper::getValue($options, 'depth');
		$option = RegistryHelper::getValue($options, 'options', 0);

		if (version_compare(PHP_VERSION, '5.5', '>'))
		{
			$depth = $depth ? : 512;

			return json_encode($object, $option, $depth);
		}

		/*
		if ($depth)
		{
			throw new \InvalidArgumentException('Depth in json_encode() only support higher than PHP 5.5');
		}
		*/

		return json_encode($object, $option);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * @param   string  $data     JSON formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 */
	public static function stringToObject($data, array $options = array())
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
}
