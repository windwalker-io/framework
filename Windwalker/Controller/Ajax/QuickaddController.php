<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Ajax;

use JForm;
use JLoader;
use JTable;
use Windwalker\Controller\DisplayController;
use Windwalker\Helper\ArrayHelper;
use Windwalker\Helper\LanguageHelper;
use Windwalker\Model\CrudModel;
use Windwalker\Model\Exception\ValidateFailException;
use Joomla\Registry\Registry;

/**
 * Class QuickaddController
 *
 * @since 1.0
 */
class QuickaddController extends DisplayController
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
		$result = new Registry;
		$result->set('Result', false);

		$model_name = $this->input->get('model_name');
		$component  = $this->input->get('component');
		$extension  = $this->input->get('extension');

		// Include Needed Classes
		JLoader::registerPrefix(ucfirst($component), JPATH_BASE . "/components/com_{$component}");
		JForm::addFormPath(JPATH_BASE . "/components/com_{$component}/models/forms");
		JForm::addFieldPath(JPATH_BASE . "/components/com_{$component}/models/fields");
		JTable::addIncludePath(JPATH_BASE . "/components/com_{$component}/tables");
		LanguageHelper::loadLanguage($extension, null);

		// Get Model
		/** @var $model CrudModel */
		$model = $this->getModel(ucfirst($model_name), ucfirst($component));

		if (!($model instanceof CrudModel))
		{
			$result->set('errorMsg', 'Model need extends to \\Windwalker\\Model\\CrudModel.');

			jexit($result);
		}

		// For WindWalker Component only
		if (method_exists($model, 'getFieldsName'))
		{
			$fields_name = $model->getFieldsName();
			$data        = ArrayHelper::pivotToTwoDimension($data, $fields_name);
		}

		// Check for validation errors.
		try
		{
			// Get Form
			if (method_exists($model, 'getForm'))
			{
				$form = $model->getForm($data, false);

				if (!$form)
				{
					$result->set('errorMsg', 'No form');

					jexit($result);
				}

				// Test whether the data is valid.
				$validData = $model->validate($form, $data);
			}
			else
			{
				$validData = $data;
			}

			// Do Save
			$model->save($validData);
		}
		catch (ValidateFailException $e)
		{
			// Get the validation messages.
			$errors   = $e->getErrors();

			$errors = array_map(
				function($error)
				{
					return (string) $error->getMessage();
				},
				$errors
			);

			$result->set('errorMsg', $errors);

			exit($result);
		}
		catch (\Exception $e)
		{
			// Return Error Message.
			$result->set('errorMsg', \JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $e->getMessage()));

			jexit($result);
		}

		// Set ID
		$data['id'] = $model->getState()->get($model_name . '.id');

		// Set Result
		$result->set('Result', true);
		$result->set('data', $data);

		jexit($result);
	}
}
