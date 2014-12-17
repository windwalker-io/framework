<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom\SimpleXml;

/**
 * Simple Xml Helper to get attributes from \SimpleXMLElement
 *
 * @since 2.0
 */
class XmlHelper
{
	/**
	 * The value of false.
	 *
	 * @var  array
	 */
	static protected $falseValue = array(
		'disabled',
		'false',
		'null',
		'0',
		'no',
		'none'
	);

	/**
	 * The value of true.
	 *
	 * @var  array
	 */
	static protected $trueValue = array(
		'true',
		'yes',
		'1'
	);

	/**
	 * Get attribute from SimpleXMLElement.
	 *
	 * @param \SimpleXMLElement $xml     A SimpleXMLElement object.
	 * @param string            $attr    The attribute name.
	 * @param mixed             $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public static function getAttribute(\SimpleXMLElement $xml, $attr, $default = null)
	{
		if (!isset($xml[$attr]))
		{
			return $default;
		}

		return (string) $xml[$attr];
	}

	/**
	 * Get attribute from SimpleXMLElement. Alias of `getAttribute()`.
	 *
	 * @param \SimpleXMLElement $xml     A SimpleXMLElement object.
	 * @param string            $attr    The attribute name.
	 * @param mixed             $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public static function get(\SimpleXMLElement $xml, $attr, $default = null)
	{
		return self::getAttribute($xml, $attr, $default);
	}

	/**
	 * Method to convert some string like `true`, `1`, `yes` to boolean TRUE,
	 * and `no`, `false`, `disabled`, `null`, `none`, `0` string to boolean FALSE.
	 *
	 * @param \SimpleXMLElement $xml     A SimpleXMLElement object.
	 * @param string            $attr    The attribute name.
	 * @param mixed             $default The default value.
	 *
	 * @return mixed The return value of this attribute.
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
	 * Just an alias of `getBool()` but FALSE will return TRUE.
	 *
	 * @param \SimpleXMLElement $xml     A SimpleXMLElement object.
	 * @param string            $attr    The attribute name.
	 * @param mixed             $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public static function getFalse(\SimpleXMLElement $xml, $attr, $default = null)
	{
		return !self::getBool($xml, $attr, $default);
	}

	/**
	 * Get all attributes.
	 *
	 * @param \SimpleXMLElement $xml A SimpleXMLElement object.
	 *
	 * @return  array The return values of all attributes.
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
	 * If this attribute not exists, use this value as default, or we use original value from xml.
	 *
	 * @param \SimpleXMLElement $xml     A SimpleXMLElement object.
	 * @param string            $attr    The attribute name.
	 * @param string            $value   The value to set as default.
	 *
	 * @return  void
	 */
	public static function def(\SimpleXMLElement $xml, $attr, $value)
	{
		$value = (string) $value;
		$attr  = (string) $attr;

		$xml[$attr] = isset($xml[$attr]) ? (string) $xml[$attr] : (string) $value;
	}
}
