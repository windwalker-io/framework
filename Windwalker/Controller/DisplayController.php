<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller;

use Windwalker\View\Html\AbstractHtmlView;

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
	protected $defaultView;

	/**
	 * Property cachable.
	 *
	 * @var boolean
	 */
	protected $cachable = false;

	/**
	 * Property urlParams.
	 *
	 * @var array
	 */
	protected $urlParams = array();

	/**
	 * Execute.
	 *
	 * @return  mixed  A rendered view or true
	 */
	protected function doExecute()
	{
		// Get some data.
		$document   = \JFactory::getDocument();
		$viewName   = $this->input->get('view', $this->defaultView);
		$viewFormat = $document->getType();
		$layoutName = $this->input->getString('layout', 'default');

		// Get View and register Model to it.
		$view = $this->getView($viewName, $viewFormat);

		$this->assignModel($view);

		// Set template layout to view.
		if ($view instanceof AbstractHtmlView)
		{
			$view->setLayout($layoutName);
		}

		// Push JDocument to View
		$view->document = $document;

		// Display the view
		$conf = \JFactory::getConfig();

		if ($this->cachable && $viewFormat != 'feed' && $conf->get('caching') >= 1)
		{
			$option = $this->input->get('option');
			$cache = \JFactory::getCache($option, 'view');

			// Register url params for JCache.
			if (is_array($this->urlParams))
			{
				if (!empty($this->app->registeredurlparams))
				{
					$registeredurlparams = $this->app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new \StdClass;
				}

				foreach ($this->urlParams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}

				$this->app->registeredurlparams = $registeredurlparams;
			}

			return $cache->get($view, 'render');
		}

		return $view->render();
	}

	/**
	 * getCachable
	 *
	 * @return boolean
	 */
	public function getCachable()
	{
		return $this->cachable;
	}

	/**
	 * setCachable
	 *
	 * @param boolean $cachable
	 *
	 * @return $this
	 */
	public function setCachable($cachable)
	{
		$this->cachable = $cachable;

		return $this;
	}

	/**
	 * getUrlParams
	 *
	 * @return array
	 */
	public function getUrlParams()
	{
		return $this->urlParams;
	}

	/**
	 * setUrlParams
	 *
	 * @param array $urlParams
	 *
	 * @return $this
	 */
	public function setUrlParams($urlParams)
	{
		$this->urlParams = $urlParams;

		return $this;
	}

	/**
	 * getView
	 *
	 * @param null $name
	 * @param null $type
	 * @param bool $forceNew
	 *
	 * @return mixed
	 */
	public function getView($name = null, $type = null, $config = array(), $forceNew = false)
	{
		// Get the name.
		if (!$name)
		{
			$name = $this->getName();
		}

		$container = $this->getContainer();

		// Get View
		$type     = ucfirst($type);
		$prefix   = ucfirst($this->getPrefix()) . 'View';
		$viewName = $prefix . ucfirst($name) . $type;

		if (!class_exists($viewName))
		{
			$viewName = '\\Windwalker\\View\\' . $type . '\\' . $type . 'View';
		}

		// Load view
		if (!class_exists($viewName))
		{
			return null;
		}

		$model  = $this->getModel($name);
		$paths  = $this->getTemplatePath($name);

		$defaultConfig = array(
			'name'   => strtolower($name),
			'option' => strtolower($this->option),
			'prefix' => strtolower($this->getPrefix())
		);

		$config = array_merge($defaultConfig, $config);

		try
		{
			$view = $container->get('view.' . strtolower($name), $forceNew);
		}
		catch (\InvalidArgumentException $e)
		{
			$container->alias('view.' . strtolower($name), $viewName)
				->share(
					$viewName,
					function($container) use($viewName, $model, $paths, $config)
					{
						return new $viewName($model, $container, $config, $paths);
					}
				);

			$view = $container->get($viewName);
		}

		return $view;
	}

	/**
	 * getTemplatePath
	 *
	 * @param \JView $view
	 *
	 * @return \SplPriorityQueue
	 */
	public function getTemplatePath($view)
	{
		// Register the layout paths for the view
		$componentFolder = $this->getComponentPath();
		$paths = new \SplPriorityQueue;

		$view = $view ?: $this->defaultView;

		// Theme override path.
		$paths->insert(JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/' . $this->option . '/' . $view, 200);

		// View tmpl path.
		$paths->insert($componentFolder . '/view/' . $view . '/tmpl', 100);

		return $paths;
	}

	/**
	 * assignModels
	 *
	 * @param \JView $view
	 *
	 * @return void
	 */
	protected function assignModel($view)
	{
	}

	/**
	 * getDefaultView
	 *
	 * @return string
	 */
	public function getDefaultView()
	{
		if (!$this->defaultView)
		{
			$this->defaultView = $this->getName();
		}

		return $this->defaultView;
	}
}
