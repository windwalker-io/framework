<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Structure\Format;

use Windwalker\Structure\StructureHelper;

/**
 * PHP class format handler for Structure
 *
 * @since  2.0
 */
class PhpFormat implements FormatInterface
{
	/**
	 * Converts an object into a php class string.
	 * - NOTE: Only one depth level is supported.
	 *
	 * @param   object  $struct   Data Source Object
	 * @param   array   $options  Parameters used by the formatter
	 *
	 * @return  string
	 */
	public static function structToString($struct, array $options = array())
	{
		$header = StructureHelper::getValue($options, 'header');

		// Build the object variables string
		$vars = "";

		foreach ($struct as $k => $v)
		{
			if (is_scalar($v))
			{
				$vars .= sprintf("\t'%s' => '%s',\n", $k, addcslashes($v, '\\\''));
			}
			elseif (is_array($v) || is_object($v))
			{
				$vars .= sprintf("\t'%s' => %s,\n", $k, static::getArrayString((array) $v));
			}
		}

		$str = "<?php\n";

		if ($header)
		{
			$str .= $header . "\n";
		}

		$str .= "\nreturn array(\n";
		$str .= $vars;
		$str .= ");\n";

		// Use the closing tag if set to true in parameters.
		if (StructureHelper::getValue($options, 'closingtag', false))
		{
			$str .= "\n?>";
		}

		return $str;
	}

	/**
	 * Parse a PHP class formatted string and convert it into an object.
	 *
	 * @param   string  $data     PHP Class formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 */
	public static function stringToStruct($data, array $options = array())
	{
		return $data;
	}

	/**
	 * Method to get an array as an exported string.
	 *
	 * @param   array  $a  The array to get as a string.
	 *
	 * @return  array
	 */
	protected static function getArrayString($a, $level = 2)
	{
		$s = "array(\n";
		$i = 0;

		foreach ($a as $k => $v)
		{
			$s .= ($i) ? ",\n" : '';
			$s .= str_repeat("\t", $level) . '"' . $k . '" => ';

			if (is_array($v) || is_object($v))
			{
				$s .= static::getArrayString((array) $v, $level + 1);
			}
			else
			{
				$s .= '"' . addslashes($v) . '"';
			}

			$i++;
		}

		$s .= "\n" . str_repeat("\t", $level - 1) . ")";

		return $s;
	}
}
