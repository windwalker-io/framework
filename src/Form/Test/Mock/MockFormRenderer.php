<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Test\Mock;

use Windwalker\Dom\HtmlElement;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Renderer\FormRendererInterface;

/**
 * The MockFormRenderer class.
 *
 * @since  {DEPLOY_VERSION}
 */
class MockFormRenderer implements FormRendererInterface
{
	/**
	 * renderField
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderField(AbstractField $field, array $attribs = array())
	{
		return (string) new HtmlElement('mock', array(
			$field->renderLabel(),
			$field->renderInput(),
		), $attribs);
	}

	/**
	 * renderLabel
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderLabel(AbstractField $field, array $attribs = array())
	{
		return 'Hello ';
	}

	/**
	 * renderInput
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderInput(AbstractField $field, array $attribs = array())
	{
		return 'World: ' . $field->getFieldName();
	}
}
