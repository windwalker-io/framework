<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Base class for a Joomla Administrator Controller
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @package     Windwalker.Framework
 * @subpackage  Component
 */
class AKControllerAdmin extends JControllerAdmin
{
	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 */
	protected $view_list = '';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 */
	protected $view_item = '';

	/**
	 * Component name.
	 *
	 * @var string
	 */
	protected $component = '';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = null, $prefix = null, $config = array('ignore_request' => true))
	{
		$name   = $name ? $name : ucfirst($this->view_item);
		$prefix = $prefix ? $prefix : ucfirst($this->component) . 'Model';

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return    bool    False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$extension = $this->input->get('extension');
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

		// Initialise variables.
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Rebuild succeeded.
			$this->setMessage(JText::_('JTOOLBAR_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::_('JLIB_DATABASE_ERROR_REBUILD_FAILED'));

			return false;
		}
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return    void
	 */
	public function saveOrderAjax()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		foreach ($order as &$row)
		{
			if ($row < 0)
			{
				$row = -$row;
			}
		}

		if (JDEBUG)
		{
			echo 'IDS: ';
			print_r($pks);
			echo 'ORDER: ';
			print_r($order);
		}

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "Order OK.";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return    void
	 */
	public function saveOrderNestedAjax()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the arrays from the Request
		$pks           = $this->input->post->get('cid', null, 'array');
		$order         = $this->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $this->input->getString('original_order_values'));

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		foreach ($order as &$row)
		{
			if ($row < 0)
			{
				$row = -$row;
			}
		}

		if (JDEBUG)
		{
			echo 'IDS: ';
			print_r($pks);
			echo 'ORDER: ';
			print_r($order);
		}

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			// Get the model
			$model = $this->getModel();

			// Save the ordering
			$return = $model->saveorderNested($pks, $order);

			if ($return)
			{
				echo "Nested order OK.";
			}
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to clone an existing module.
	 *
	 * @return  bool
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = $this->input->getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
			}

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_' . $this->component . '&view=' . $this->view_list);

		return true;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  JController  This object to support chaining.
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$task           = $this->getTask();
		$redirect_tasks = $this->redirect_tasks;

		if (!$this->redirect)
		{
			$this->redirect = AKHelper::_('uri.base64', 'decode', JRequest::getVar('return'));
		}

		if ($this->redirect && in_array($task, $redirect_tasks))
		{
			return parent::setRedirect($this->redirect, $msg, $type);
		}
		else
		{
			return parent::setRedirect($url, $msg, $type);
		}
	}

	/**
	 * Handle QuickEdit Ajax command and print JSON result.
	 *
	 * @return void
	 */
	public function editFieldData()
	{
		$id    = $this->input->get('id');
		$field = $this->input->get('field');
		//$table     = JRequest::getVar('table') ;
		$content = $this->input->getVar('content');

		$model = $this->getModel();
		$table = $model->getTable();
		$table->load($id);
		$result = 'false';

		if (property_exists($table, $field))
		{
			$table->$field = $content;
			$table->check();

			if (!$table->store())
			{
				$result = 'false';
			}
			else
			{
				$result = 'true';
			}
		}

		echo '{"AKResult":' . $result . ', "message" : "' . $table->getError() . '"}';
		jexit();
	}
}