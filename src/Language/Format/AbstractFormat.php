<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Format;

use Windwalker\Language\LanguageNormalize;

/**
 * Class AbstractFormat
 *
 * @since 2.0
 */
abstract class AbstractFormat implements FormatInterface
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = '';

	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * parse
	 *
	 * @param string $string
	 *
	 * @return  array
	 */
	abstract public function parse($string);

	/**
	 * Dump to on dimension array.
	 *
	 * @param array  $data      The data to convert.
	 * @param string $separator The key separator.
	 *
	 * @return  string[] Dumped array.
	 */
	protected function toOneDimension($data, $separator = '_')
	{
		$array = array();

		$this->asOneDimension($separator, $data, $array);

		return $array;
	}

	/**
	 * Method to recursively convert data to one dimension array.
	 *
	 * @param string        $separator The key separator.
	 * @param array|object  $data      Data source of this scope.
	 * @param array         &$array    The result array, it is pass by reference.
	 * @param string        $prefix    Last level key prefix.
	 *
	 * @return  void
	 */
	protected function asOneDimension($separator = '_', $data = null, &$array = array(), $prefix = '')
	{
		$data = (array) $data;

		foreach ($data as $k => $v)
		{
			$key = $prefix ? $prefix . $separator . $k : $k;

			if (is_object($v) || is_array($v))
			{
				$this->asOneDimension($separator, $v, $array, $key);
			}
			else
			{
				$array[$key] = $v;
			}
		}
	}
}

