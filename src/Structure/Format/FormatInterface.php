<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Structure\Format;

/**
 * Class StructureFormatInterface
 *
 * @since 2.0
 */
interface FormatInterface
{
	/**
	 * Converts an object into a formatted string.
	 *
	 * @param   object  $struct   Data Source Object.
	 * @param   array   $options  An array of options for the formatter.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   2.0
	 */
	static public function structToString($struct, array $options = array());

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param   string  $data     Formatted string
	 * @param   array   $options  An array of options for the formatter.
	 *
	 * @return  object  Data Object
	 *
	 * @since   2.0
	 */
	static public function stringToStruct($data, array $options = array());
}
