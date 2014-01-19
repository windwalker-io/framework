<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul;

use Windwalker\Data\Data;
use Windwalker\String\String;
use Windwalker\Helper\XmlHelper;
use Windwalker\Html\HtmlElements;

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
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	public static function render($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		$result = static::prepareRender($name, $engine, $element, $data);

		if (!$result)
		{
			return false;
		}

		$html = static::doRender($name, $engine, $element, $data);

		return static::postRender($html, $name, $engine, $element, $data);
	}

	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		throw new \LogicException('Please override render() method in your renderer');
	}

	/**
	 * prepareRender
	 *
	 * @param string            $name
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  bool
	 */
	protected static function prepareRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		return true;
	}

	/**
	 * postRender
	 *
	 * @param string            $html
	 * @param string            $name
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function postRender($html, $name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		return $html;
	}

	/**
	 * renderChildren
	 *
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  string
	 */
	public static function renderChildren(XulEngine $engine, $element, $data)
	{
		$html = new HtmlElements;

		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		$data = clone $data;

		$children = $element->xpath('*');

		if (count($children))
		{
			foreach ($children as $child)
			{
				// Replace all attributes with variable.
				foreach ($child->attributes() as $key => $attr)
				{
					$child[$key] = String::parseVariable((string) $attr, $data);
				}

				$namespaces    = $child->getNamespaces();
				$name = $class = $child->getName();

				$ns = 'Control';

				if (array_key_exists('html', $namespaces))
				{
					$ns    = 'Html';
					$class = 'Html';
				}

				$renderer = XmlHelper::get($child, 'renderer');

				if (!$renderer || !is_subclass_of($renderer, __CLASS__))
				{
					$prefix = $data->xulControl->classPrefix;

					$renderer = $engine->findRenderer($class, $ns, $prefix);
				}

				$html[] = call_user_func_array(array($renderer, 'render'), array($name, $engine, $child, $data));
			}
		}
		else
		{
			$html = String::parseVariable((string) $element, $data);
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
			$attr = String::parseVariable($attr, $data);
		}

		return $attributes;
	}

	/**
	 * getParsedAttributes
	 *
	 * @param \SimpleXmlElement $element
	 * @param Data              $data
	 *
	 * @return  mixed
	 */
	protected static function getParsedAttributes($element, $data)
	{
		$attributes = XmlHelper::getAttributes($element);

		return static::replaceVariable($attributes, $data);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}
}
