<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use Windwalker\Model\Exception\ValidateFailException;

defined('JPATH_PLATFORM') or die;

/**
 * Prototype form model.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @see         JForm
 * @see         JFormField
 * @see         JFormRule
 * @since       3.2
 */
abstract class FormModel extends AbstractAdvancedModel
{
	/**
	 * Array of form objects.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $forms = array();

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$config = array(
			'control'   => 'jform',
			'load_data' => $loadData
		);

		return $this->loadForm($this->option . '.' . $this->getName() . '.form', $this->getName(), $config);
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string    $name    The name of the form.
	 * @param   string    $source  The form source. Can be XML string if file flag is set to false.
	 * @param   array     $options Optional array of options for the form creation.
	 * @param   boolean   $clear   Optional argument to force load a new form.
	 * @param   string    $xpath   An optional xpath to search for the fields.
	 *
	 * @throws \Exception
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 * @since   3.2
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = null)
	{
		// Handle the optional arguments.
		$options['control'] = \JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = sha1($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->forms[$hash]) && !$clear)
		{
			return $this->forms[$hash];
		}

		// Set Form paths
		static $formLoaded;

		if (!$formLoaded)
		{
			// Get the form.
			// Register the paths for the form
			$paths = new \SplPriorityQueue;
			$paths->insert(JPATH_COMPONENT . '/model/form', 'normal');
			$paths->insert(JPATH_COMPONENT . '/model/field', 'normal');
			$paths->insert(JPATH_COMPONENT . '/model/rule', 'normal');

			// Legacy support to be removed in 4.0.
			$paths->insert(JPATH_COMPONENT . '/models/forms', 'normal');
			$paths->insert(JPATH_COMPONENT . '/models/fields', 'normal');
			$paths->insert(JPATH_COMPONENT . '/models/rules', 'normal');

			\JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
			\JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
			\JForm::addFormPath(JPATH_COMPONENT . '/model/form');
			\JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

			// Set Form paths for Windwalker
			\JForm::addFormPath(JPATH_COMPONENT . '/model/form/' . strtolower($this->getName()));

			// \JForm::addFieldPath(JPATH_COMPONENT . '/model/field/' . strtolower($this->getName()));

			$formLoaded = true;
		}

		try
		{
			$form = \JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		}
		catch (\Exception $e)
		{
			throw $e;
		}

		// Store the form for later.
		$this->forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   3.2
	 */
	protected function loadFormData()
	{
		$container = $this->getContainer();
		$app   = $container->get('app');
		$input = $container->get('input');

		// Check the session for previously entered form data.
		$data = $app->getUserState("{$this->option}.edit.{$this->getName()}.data", array());

		if (empty($data))
		{
			$data = $this->getItem();
		}
		else
		{
			$data = $data;

			// If Error occured and resend, just return data.
			return $data;
		}

		// If page reload, retain data
		// ==========================================================================================
		$retain = $input->get('retain', 0);

		// Set Change Field Type Retain Data
		if ($retain)
		{
			$data = $input->getVar('jform');
		}

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function preprocessData($context, &$data)
	{
		// Get the dispatcher and load the users plugins.
		$dispatcher = $this->getContainer()->get('event.dispatcher');

		\JPluginHelper::importPlugin('content');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   \JForm  $form  A JForm object.
	 * @param   mixed   $data  The data expected for the form.
	 * @param   string  $group The name of the plugin group to import (defaults to "content").
	 *
	 * @throws  \Exception if there is an error in the form event.
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   3.2
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		\JPluginHelper::importPlugin($group);

		// Get the dispatcher.
		$dispatcher = $this->getContainer()->get('event.dispatcher');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof \Exception))
			{
				throw new \Exception($error);
			}
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \JForm  $form  The form to validate against.
	 * @param   array   $data  The data to validate.
	 * @param   string  $group The name of the field group to validate.
	 *
	 * @throws  VaildateFailExcption
	 * @throws  \Exception
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   3.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		/** @var $form \JForm */
		$data   = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			throw $return;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			throw new ValidateFailException($form->getErrors());
		}

		return $data;
	}
}
