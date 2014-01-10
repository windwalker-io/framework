<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul;

use Windwalker\Data\Data;
use Windwalker\Helper\StringHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Html\HtmlElements;
use Windwalker\View\Engine\AbstractEngine;
use Windwalker\Xul\Html\HtmlRenderer;

/**
 * Class XulEngine
 *
 * @since 1.0
 */
class XulEngine extends AbstractEngine
{
	/**
	 * @var  string  Property layoutExt.
	 */
	protected $layoutExt = 'xml';

	/**
	 * execute
	 *
	 * @param string $templateFile
	 * @param null   $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	protected function execute($templateFile, $data = null)
	{
		if (!is_file($templateFile))
		{
			throw new \InvalidArgumentException(sprintf('Template "%s" not exists.', $templateFile));
		}

		$xml = simplexml_load_file($templateFile);

		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		$data->xulControl = new Data;

		return HtmlRenderer::render('div', $xml, $data);
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
					$prefix = $data->xulControl->classPrefix;

					$renderer = '\\Windwalker\\Xul\\' . $ns . '\\' . $prefix . ucfirst($class) . 'Renderer';

					if (!class_exists($renderer))
					{
						$renderer = '\\Windwalker\\Xul\\' . $ns . '\\' . ucfirst($class) . 'Renderer';
					}

					if (!class_exists($renderer))
					{
						throw new \DomainException(sprintf('Xul tag: "%s" do not support.', $name));
					}
				}

				$html[] = call_user_func_array(array($renderer, 'render'), array($name, $child, $data));
			}
		}
		else
		{
			$html = StringHelper::parseVariable((string) $element, $data);
		}

		return $html;
	}
}
