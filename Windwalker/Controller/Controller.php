<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller;

use JInput;
use JApplicationBase;

/**
 * Class Controller
 *
 * @since 2.0
 */
class Controller extends \JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	protected $prefix = '';

	/**
	 * Property option.
	 *
	 * @var string
	 */
	protected $option = '';

	/**
	 * Permission needed for the action. Defaults to most restrictive
	 *
	 * @var  string
	 */
	protected $permission = 'core.admin';

	/**
	 * Property componentPath.
	 *
	 * @var string
	 */
	protected $componentPath = '';

	/**
	 * Property reflection.
	 *
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Property defaultView.
	 *
	 * @var string
	 */
	protected $defaultView = 'items';

	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);
	}

	/**
	 * execute
	 *
	 * @return $this|bool
	 */
	public function execute()
	{
	}

	/**
	 * getComponentPath
	 *
	 * @return string
	 */
	public function getComponentPath()
	{
		return $this->componentPath;
	}

	/**
	 * setComponentPath
	 *
	 * @param string $componentPath
	 *
	 * @return $this
	 */
	public function setComponentPath($componentPath)
	{
		$this->componentPath = $componentPath;

		return $this;
	}

	/**
	 * getReflection
	 *
	 * @return \ReflectionClass
	 */
	public function getReflection()
	{
		if ($this->reflection)
		{
			return $this->reflection;
		}

		$this->reflection = new \ReflectionClass($this);

		return $this->reflection;
	}



	/**
	 * getPrefix
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * setPrefix
	 *
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		if ($this->name !== null)
		{
			return $this->name;
		}

		$ref = $this->getReflection();

		$name = explode('Controller', $ref->getName());

		if ($name[0] == $this->getPrefix())
		{
			return $this->name = '';
		}
		elseif (!empty($name[1]))
		{
			return $this->name = trim($name[1], '\\');
		}

		return '';
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param string $option
	 */
	public function setOption($option)
	{
		$this->option = $option;

		return $this;
	}

	/**
	 * checkToken
	 *
	 * @return void
	 */
	protected function checkToken()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));
	}

	public function getView($name = null, $type = null, \JModel $model = null)
	{
		if (!$name)
		{
			$name = $this->getName();
		}

		if (!$model)
		{
			$model = $this->getModel($name);
		}

		$type = ucfirst($type);

		$prefix = ucfirst($this->getPrefix()) . 'View' . $type;

		$viewName = $prefix . ucfirst($name);

		if (!class_exists($viewName))
		{
			$viewName = '\\Windwalker\\View\\' . $type . '\\View' . $type;
		}

		$paths = $this->getTemplatePath($name);

		$view = new $viewName($model, $paths);

		$view->setName($name);

		return $view;
	}

	/**
	 * getModel
	 *
	 * @param null  $name
	 * @param null  $prefix
	 * @param array $config
	 *
	 * @return mixed
	 */
	public function getModel($name = null, $prefix = null, $config = array())
	{
		if (!$name)
		{
			$name = $this->getName();
		}

		if (!$prefix)
		{
			$prefix = ucfirst($this->getPrefix()) . 'Model';
		}

		$modelName = $prefix . ucfirst($name);

		if (!class_exists($modelName))
		{
			$modelName = '\\Windwalker\\Model\\Model';
		}

		$model = new $modelName;

		$model->setName($name)
			->setOption('com_' . $this->option);

		return $model;
	}

	/**
	 * getTemplatePath
	 *
	 * @param $view
	 *
	 * @return \SplPriorityQueue
	 */
	public function getTemplatePath($view)
	{
		// Register the layout paths for the view
		$componentFolder = $this->getComponentPath();
		$paths = new \SplPriorityQueue;

		$view = $view ?: $this->defaultView;

		// View tmpl path.
		$paths->insert($componentFolder . '/view/' . $view . '/tmpl', 'normal');

		// Theme override path.
		$paths->insert(JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/' . $this->option . '/' . $view, 'normal');

		return $paths;
	}
}
