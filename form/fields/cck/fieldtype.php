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
JFormHelper::loadFieldType('List');

/**
 * Supports an HTML select list of Fieldtype.
 *
 * @package     Windwalker.Framework
 * @subpackage  Form.CCK
 */
class JFormFieldFieldtype extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 */
	public $type = 'Fieldtype';

	public $value;

	public $name;

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 */
	public function getOptions()
	{
		//$this->value = JRequest::getVar('field_type') ;
		$this->setFieldData();

		if (!$this->value)
		{
			$this->value = (string) $this->element['default'];
		}

		JRequest::setVar('field_type', $this->value, 'method', true);

		$element = $this->element;

		$types = JFolder::files(AKPATH_FORM . '/forms/attr');

		JFactory::getApplication()
			->triggerEvent('onCCKEnginePrepareFieldtypes', array(&$types, &$this, &$element));

		$options = array();

		foreach ($types as $type):
			$type = str_replace('.xml', '', $type);

			if ($type == 'index.html')
			{
				continue;
			}

			$options[] = JHtml::_(
				'select.option', (string) $type,
				JText::_('LIB_WINDWALKER_FIELDTYPE_' . strtoupper($type))
			);
		endforeach;

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * If default value exists.
	 */
	public function setFieldData()
	{
		if (!JRequest::getVar('id'))
		{
			$app = JFactory::getApplication();
			$app->setUserState('lib_windwalker.cck.fields', null);
		}

		$type = JRequest::getVar('field_type');

		$this->value = $type ? $type : $this->value;
	}
}