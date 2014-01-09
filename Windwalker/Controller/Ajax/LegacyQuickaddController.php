<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Ajax;

use Windwalker\Controller\DisplayController;
use Windwalker\Helper\ArrayHelper;
use Windwalker\Helper\LanguageHelper;

/**
 * Class QuickaddController
 *
 * @since 1.0
 */
class LegacyQuickaddController extends DisplayController
{
	/**
	 * doExecute
	 *
	 * @return mixed|void
	 */
	protected function doExecute()
	{
		// Init Variables
		$data   = $this->input->get($this->input->get('formctrl'), array(), 'array');
		$result = new \JRegistry;
		$result->set('Result', false);

		$model_name = $this->input->get('model_name');
		$component  = $this->input->get('component');
		$extension  = $this->input->get('extension');

		// Include Needed Classes
		\JModelLegacy::addIncludePath(JPATH_BASE . "/components/com_{$component}/models");
		\JForm::addFormPath(JPATH_BASE . "/components/com_{$component}/models/forms");
		\JForm::addFieldPath(JPATH_BASE . "/components/com_{$component}/models/fields");
		\JTable::addIncludePath(JPATH_BASE . "/components/com_{$component}/tables");
		LanguageHelper::loadLanguage($extension, null);

		// Get Model
		$model = \JModelLegacy::getInstance(ucfirst($model_name), ucfirst($component) . 'Model', array('ignore_request' => true));

		// For WindWalker Component only
		if (is_callable(array($model, 'getFieldsName')))
		{
			$fields_name = $model->getFieldsName();
			$data        = ArrayHelper::pivotToTwoDimension($data, $fields_name);
		}

		// Get Form
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$result->set('errorMsg', $model->getError());
			jexit($result);
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors   = $model->getErrors();
			$errorMsg = is_string($errors[0]) ? $errors[0] : $errors[0]->getMessage();
			$result->set('errorMsg', $errorMsg);
			jexit($result);
		}

		// Do Save
		if (!$model->save($validData))
		{
			// Return Error Message.
			$result->set('errorMsg', \JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			jexit($result);
		}

		// Set ID
		$data['id'] = $model->getstate($model_name . '.id');

		// Set Result
		$result->set('Result', true);
		$result->set('data', $data);
		jexit($result);
	}
}
