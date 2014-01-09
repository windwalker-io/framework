<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul;

use Windwalker\Helper\StringHelper;
use Windwalker\Helper\XmlHelper;

/**
 * Class XulRenderer
 *
 * @since 1.0
 */
abstract class AbstractXulRenderer
{
	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	public static function render($name, \SimpleXmlElement $element, $data)
	{
		$result = static::prepareRender($name, $element, $data);

		if (!$result)
		{
			return false;
		}

		$html = static::doRender($name, $element, $data);

		return static::postRender($html, $name, $element, $data);
	}

	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, \SimpleXmlElement $element, $data)
	{
		throw new \LogicException('Please override render() method in your renderer');
	}

	/**
	 * prepareRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  bool
	 */
	protected static function prepareRender($name, \SimpleXmlElement $element, $data)
	{
		return true;
	}

	/**
	 * postRender
	 *
	 * @param string            $html
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function postRender($html, $name, \SimpleXmlElement $element, $data)
	{
		return $html;
	}

	/**
	 * renderChildren
	 *
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \DomainException
	 * @return  string
	 */
	protected static function renderChildren($element, $data)
	{
		$html = '';

		$children = $element->xpath('*');

		if (count($children))
		{
			foreach ($children as $child)
			{
				$namespaces    = $child->getNamespaces();
				$name = $class = $child->getName();

				$ns = 'Control';

				if (array_key_exists('html', $namespaces))
				{
					$ns    = 'Html';
					$class = 'Html';
				}

				$handler = XmlHelper::get($child, 'handler');

				if ($handler && is_subclass_of($handler, __CLASS__))
				{
					$renderer = $handler;
				}
				else
				{
					$renderer = '\\Windwalker\\Xul\\' . $ns . '\\' . ucfirst($class) . 'Renderer';

					if (!class_exists($renderer))
					{
						throw new \DomainException(sprintf('Xul tag: "%s" do not support.', $name));
					}
				}

				if (is_object($data))
				{
					$data = clone $data;
				}

				$html .= call_user_func_array(array($renderer, 'render'), array($name, $child, $data));
			}
		}
		else
		{
			$html = StringHelper::parseVariable((string) $element, $data);
		}

		return $html;
	}

	/**
	 * replaceVariable
	 *
	 * @param $attributes
	 * @param $data
	 *
	 * @return  mixed
	 */
	protected static function replaceVariable($attributes, $data)
	{
		foreach ($attributes as &$attr)
		{
			$attr = StringHelper::parseVariable($attr, $data);
		}

		return $attributes;
	}
}
