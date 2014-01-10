<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\Helper\AssetHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

/**
 * Class AssetRenderer
 *
 * @since 1.0
 */
class AssetRenderer extends AbstractXulRenderer
{
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
		static::addFramework($element, $data);

		$type = XmlHelper::get($element, 'type');
		$file = XmlHelper::get($element, 'file');

		if (!$file)
		{
			return;
		}

		if (!$type)
		{
			$type = pathinfo($file, PATHINFO_EXTENSION);
		}

		switch ($type)
		{
			case 'css':
			case 'style':
			case 'stylesheet':
				static::addCss($file, $data);
				break;

			case 'js':
			case 'javascript':
			case 'script':
				static::addJs($file, $data);
				break;
		}
	}

	/**
	 * addFramework
	 *
	 * @param $element
	 * @param $data
	 *
	 * @return  void
	 */
	protected static function addFramework($element, $data)
	{
		$framework = XmlHelper::get($element, 'framework');
		$debug     = XmlHelper::getBool($element, 'debug');
		$asset     = $data->asset;

		if (!$framework)
		{
			return;
		}

		$names = explode(',', strtolower($framework));

		if (in_array('mootools', $names))
		{
			$asset->mootools($debug);
		}

		if (in_array('jquery', $names))
		{
			$asset->jquery($debug);
		}

		if (in_array('jquery-ui', $names))
		{
			$asset->jqueryUI($debug);
		}

		if (in_array('windwalker', $names))
		{
			$asset->windwalker($debug);
		}

		if (in_array('bootstrap', $names))
		{
			$asset->bootstrap($debug);
		}
	}


	/**
	 * addCss
	 *
	 * @param string $file
	 * @param mixed  $data
	 *
	 * @return  void
	 */
	protected static function addCss($file, $data)
	{
		$asset = $data->asset;

		if ($asset instanceof AssetHelper)
		{
			$asset->addCss($file);
		}
		else
		{
			$doc = \JFactory::getDocument();

			$doc->addStyleSheet($file);
		}
	}

	/**
	 * addJs
	 *
	 * @param string $file
	 * @param mixed  $data
	 *
	 * @return  void
	 */
	protected static function addJs($file, $data)
	{
		$asset = $data->asset;

		if ($asset instanceof AssetHelper)
		{
			$asset->addJs($file);
		}
		else
		{
			$doc = \JFactory::getDocument();

			$doc->addScript($file);
		}
	}
}
