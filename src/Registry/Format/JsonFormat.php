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
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  JSON formatted string.
	 */
	public static function objectToString($object, $options = array())
	{
		$depth  = RegistryHelper::getValue($options, 'depth', 512);
		$option = RegistryHelper::getValue($options, 'options', 0);

		return json_encode($object, $option, $depth);
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

		return json_decode(trim($data), $assoc, $depth, $option);
	}
}
