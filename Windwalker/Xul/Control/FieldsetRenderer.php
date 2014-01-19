<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\String\String;
use Windwalker\Html\HtmlBuilder;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

/**
 * Class FieldsetRenderer
 *
 * @since 1.0
 */
class FieldsetRenderer extends AbstractXulRenderer
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
		$formVar  = XmlHelper::get($element, 'form', 'form');
		$fieldset = XmlHelper::get($element, 'name');

		if (!$fieldset)
		{
			throw new \UnexpectedValueException('Need "name" attribute in XUL <fieldset> element.');
		}

		$form = $data->$formVar;

		if (!($form instanceof \JForm))
		{
			throw new \UnexpectedValueException(sprintf('No form data found in $data->%s.', $formVar));
		}

		$option = $data->view->option ? : 'LIB_WINDWALKER';
		$label  = XmlHelper::get($element, 'label', $option . '_EDIT_FIELDSET_' . $fieldset);
		$label  = String::parseVariable($label, $data);
		$html   = '<legend>' . \JText::_(strtoupper($label)) . '</legend>';

		foreach ($form->getFieldset($fieldset) as $field)
		{
			$html .= $field->getControlGroup() . "\n\n";
		}

		$html = HtmlBuilder::create('fieldset', $html, XmlHelper::getAttributes($element));

		return $html;
	}
}
