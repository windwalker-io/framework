<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Renderer;

use Windwalker\Form\Field\AbstractField;

/**
 * The FormRendererInterface class.
 *
 * @since  {DEPLOY_VERSION}
 */
interface FormRendererInterface
{
	/**
	 * renderField
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderField(AbstractField $field, array $attribs = array());

	/**
	 * renderLabel
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderLabel(AbstractField $field, array $attribs = array());

	/**
	 * renderInput
	 *
	 * @param AbstractField $field
	 * @param array         $attribs
	 *
	 * @return string
	 */
	public function renderInput(AbstractField $field, array $attribs = array());
}
