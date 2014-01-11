<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use JHtml;
use Windwalker\Html\HtmlBuilder;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;
use Windwalker\Xul\Html\HtmlRenderer;

/**
 * Class FormRenderer
 *
 * @since 1.0
 */
class FormRenderer extends AbstractXulRenderer
{
	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \UnexpectedValueException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		XmlHelper::def($element, 'action',  $data->uri->path);
		XmlHelper::def($element, 'method',  'post');
		XmlHelper::def($element, 'id',      $data->view->name . '-form');
		XmlHelper::def($element, 'name',    'adminForm');
		XmlHelper::def($element, 'class',   'form-validate');
		XmlHelper::def($element, 'enctype', 'multipart/form-data');

		$attributes = XmlHelper::getAttributes($element);

		$footerHandler = XmlHelper::get($element, 'type', 'default');
		$footerHandler = array(__CLASS__, 'render' . ucfirst($footerHandler) . 'Footer');

		// Build hidden inputs
		$footer  = HtmlBuilder::create('input', null, array('type' => 'hidden' , 'name' => 'option', 'value' => $data->view->option));
		$footer .= HtmlBuilder::create('input', null, array('type' => 'hidden' , 'name' => 'task', 'value' => ''));
		$footer .= is_callable($footerHandler) ? call_user_func_array($footerHandler, array()) : '';
		$footer .= JHtml::_('form.token');

		// Wrap inputs
		$children  = static::renderChildren($engine, $element, $data);
		$children .= HtmlBuilder::create('div', $footer, array('id' => 'hidden-inputs'));

		return HtmlBuilder::create($name, $children, $attributes);
	}

	/**
	 * renderGridFooter
	 *
	 * @return  string
	 */
	protected static function renderGridFooter()
	{
		$children = '';

		$children .= HtmlBuilder::create('input', null, array('type' => 'hidden' , 'name' => 'boxchecked', 'value' => ''));

		return $children;
	}

	/**
	 * renderGridFooter
	 *
	 * @return  string
	 */
	protected static function renderEditFooter()
	{
		$children = '';

		return $children;
	}

	/**
	 * renderGridFooter
	 *
	 * @return  string
	 */
	protected static function renderDefaultFooter()
	{
		$children = '';

		return $children;
	}
}
