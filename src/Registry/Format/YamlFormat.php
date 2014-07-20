<?php
/**
 * Part of Windwalker project
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Registry\Format;

use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Dumper as SymfonyYamlDumper;
use Windwalker\Registry\Helper\RegistryHelper;

/**
 * YAML format handler for Registry.
 *
 * @since  1.0
 */
class YamlFormat implements FormatInterface
{
	/**
	 * The YAML parser class.
	 *
	 * @var  \Symfony\Component\Yaml\Parser;
	 */
	protected static $parser;

	/**
	 * The YAML dumper class.
	 *
	 * @var  \Symfony\Component\Yaml\Dumper;
	 */
	protected static $dumper;

	/**
	 * Converts an object into a YAML formatted string.
	 * We use json_* to convert the passed object to an array.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  YAML formatted string.
	 *
	 * @since   1.0
	 */
	static public function objectToString($object, $options = array())
	{
		$array = json_decode(json_encode($object), true);

		$inline = RegistryHelper::getValue($options, 'inline', 2);
		$indent = RegistryHelper::getValue($options, 'indent', 0);

		return static::getDumper()->dump($array, $inline, $indent);
	}

	/**
	 * Parse a YAML formatted string and convert it into an object.
	 * We use the json_* methods to convert the parsed YAML array to an object.
	 *
	 * @param   string  $data     YAML formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object  Data object.
	 *
	 * @since   1.0
	 */
	static public function stringToObject($data, array $options = array())
	{
		$array = static::getParser()->parse(trim($data));

		return json_decode(json_encode($array));
	}

	/**
	 * getParser
	 *
	 * @return  \Symfony\Component\Yaml\Parser
	 */
	public static function getParser()
	{
		if (!static::$parser)
		{
			static::$parser = new SymfonyYamlParser;
		}

		return static::$parser;
	}

	/**
	 * setParser
	 *
	 * @param   \Symfony\Component\Yaml\Parser $parser
	 *
	 * @return  YamlFormat  Return self to support chaining.
	 */
	public static function setParser($parser)
	{
		static::$parser = $parser;
	}

	/**
	 * getDumper
	 *
	 * @return  \Symfony\Component\Yaml\Dumper
	 */
	public static function getDumper()
	{
		if (!static::$dumper)
		{
			static::$dumper = new SymfonyYamlDumper;
		}

		return static::$dumper;
	}

	/**
	 * setDumper
	 *
	 * @param   \Symfony\Component\Yaml\Dumper $dumper
	 *
	 * @return  YamlFormat  Return self to support chaining.
	 */
	public static function setDumper($dumper)
	{
		static::$dumper = $dumper;
	}
}
