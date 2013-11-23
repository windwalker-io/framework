<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Form.CCK
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JForm::addFieldPath(AKPATH_FORM . '/fields');

/**
 * Supports an HTML select list of CCK fields
 *
 * @package     Windwalker.Framework
 * @subpackage  Form.CCK
 */
class JFormFieldFields extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Fields';

	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var JForm
	 */
	public $form;

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 */
	public function getInput()
	{
		static $form_setted = false;
		static $form;

		$this->getValues();
		$this->addFieldJs();

		$element = $this->element;
		$class   = (string) $element['class'];
		$nolabel = (string) $element['nolabel'];
		$nolabel = ($nolabel == 'true' || $nolabel == '1') ? true : false;

		// Get Field Form
		// =============================================================
		if (!$form_setted)
		{
			// ParseValue
			$data = AKHelper::_('fields.parseAttrs', $this->value);

			$type = JRequest::getVar('field_type', 'text');
			$form = null;

			// Loading form
			// =============================================================
			JForm::addFormPath(AKPATH_FORM . '/forms/attr');
			$form = null;

			// Event
			JFactory::getApplication()
				->triggerEvent('onCCKEngineBeforeFormLoad', array(&$form, &$data, &$this, &$element, &$form_setted));

			$form = JForm::getInstance('fields', $type, array('control' => 'attrs'), false, false);

			// Event
			JFactory::getApplication()
				->triggerEvent('onCCKEngineAfterFormLoad', array(&$form, &$data, &$this, &$element, &$form_setted));

			$form->bind($data);

			// Set Default for Options
			$default = JArrayHelper::getValue($data, 'default');
			JRequest::setVar('field_default', $default, 'method', true);
			$form_setted = true;
		}

		$fieldset = (string) $element['fset'];
		$fieldset = $fieldset ? $fieldset : 'attrs';
		$fields   = $form->getFieldset($fieldset);

		$html = '<div class="' . $class . ' ak-cck-' . $fieldset . '">';

		foreach ($fields as $field):
			if (!$nolabel)
			{
				$html .= '<div class="control-group">';
				$html .= '    <div class="control-label">' . $field->getLabel() . '</div>';
				$html .= '            <div class="controls">' . $field->getInput() . '</div>';
				$html .= '</div>';
			}
			else
			{
				$html .= '<div class="control-group">';
				$html .= $field->getInput();
				$html .= '</div>';
			}
		endforeach;

		$html .= '</div>';

		return $html;

	}

	/**
	 * Get values from session.
	 */
	public function getValues()
	{
		if ($this->value)
		{
			return true;
		}

		$attrs = JFactory::getApplication()->getUserState("lib_windwalker.cck.fields", array());

		if ($attrs)
		{
			$this->value = json_encode($attrs);
		}

		// Retain data
		$retain = JRequest::getVar('retain', 0);

		if ($retain)
		{
			$this->value = json_encode(JRequest::getVar('attrs'));
		}
	}

	/**
	 * Add JS to head.
	 */
	public function addFieldJs()
	{
		$doc       = JFactory::getDocument();
		$akpath_js = JPath::clean(AKPATH_ASSETS);
		$js_url    = str_replace(JPATH_ROOT, '', $akpath_js) . '/js/fields.js';
		$js_url    = trim($js_url, '/');
		$js_url    = JRoute::_('../' . $js_url);

		$doc->addScript($js_url);
	}
}