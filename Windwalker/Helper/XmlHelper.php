<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class XmlHelper
 *
 * @since 1.0
 */
class XmlHelper
{
	static protected $falseValue = array(
		'disbaled',
		'false',
		'null',
		'0',
		'no',
		'none'
	);

	static protected $trueValue = array(
		'true',
		'yes',
		'1'
	);

	/**
	 * getAttribute
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string            $attr
	 * @param null              $default
	 *
	 * @return mixed
	 */
	public static function getAttribute(\SimpleXMLElement $xml, $attr, $default = null)
	{
		$value = (string) $xml[$attr];

		if (!$value)
		{
			return $default;
		}

		return $value;
	}

	/**
	 * geetAttr
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string            $attr
	 * @param null              $default
	 *
	 * @return bool
	 */
	public static function get(\SimpleXMLElement $xml, $attr, $default = null)
	{
		return self::getAttribute($xml, $attr, $default);
	}

	/**
	 * getBool
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string            $attr
	 * @param null              $default
	 *
	 * @return boolean
	 */
	public static function getBool(\SimpleXMLElement $xml, $attr, $default = null)
	{
		$value = self::getAttribute($xml, $attr, $default);

		if (in_array((string) $value, self::$falseValue) || !$value)
		{
			return false;
		}

		return true;
	}

	/**
	 * getFalse
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string            $attr
	 * @param null              $default
	 *
	 * @return bool
	 */
	public static function getFalse(\SimpleXMLElement $xml, $attr, $default = null)
	{
		return !self::getBool($xml, $attr, $default);
	}

	/**
	 * getAttributes
	 *
	 * @param \SimpleXMLElement $xml
	 *
	 * @return  array
	 */
	public static function getAttributes(\SimpleXMLElement $xml)
	{
		$attributes = array();

		foreach ($xml->attributes() as $name => $value)
		{
			$attributes[$name] = (string) $value;
		}

		return $attributes;
	}

	/**
	 * set
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string            $attr
	 * @param string            $value
	 *
	 * @return  void
	 */
	public static function def(\SimpleXMLElement $xml, $attr, $value)
	{
		$value = (string) $value;
		$attr  = (string) $attr;

		$xml[$attr] = isset($xml[$attr]) ? $xml[$attr] : (string) $value;
	}
}
