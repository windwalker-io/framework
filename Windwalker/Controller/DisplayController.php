<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller;

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
 */
class DisplayController extends Controller
{
	/**
	 * Property defaultView.
	 *
	 * @var string
	 */
	protected $defaultView = 'items';

	/**
	 * Execute.
	 *
	 * @return  mixed  A rendered view or true
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		!$app->isAdmin() ? : $this->permission = 'core.manage';

		// Get the document object.
		$document     = \JFactory::getDocument();

		$componentFolder = $this->getComponentPath();
		$viewName        = $this->input->getWord('view', $this->defaultView);
		$viewFormat      = $document->getType();
		$layoutName      = $this->input->getWord('layout', 'default');

		// Register the layout paths for the view
		$paths = new \SplPriorityQueue;
		$paths->insert($componentFolder . '/view/' . $viewName . '/tmpl', 'normal');

		$viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		if (class_exists($viewClass))
		{
			$model = new $modelClass;

			// Access check.
			if (!empty($this->permission) && !JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
			{
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;
			}

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Reply for service requests
			if ($viewFormat == 'json')
			{

				return $view->render();
			}

			// Render view.
			echo $view->render();
		}

		return true;
	}
}