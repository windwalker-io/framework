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
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

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
	 * Property model.
	 *
	 * @var array
	 */
	protected $model = array();

	/**
	 * Property view.
	 *
	 * @var array
	 */
	protected $view = array();

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
		// Get name.
		if (!$name)
		{
			$name = $this->getName();
		}

		// Get from cache.
		if (!empty($this->model[$name]))
		{
			return $this->model[$name];
		}

		// Get Prefix
		if (!$prefix)
		{
			$prefix = ucfirst($this->getPrefix()) . 'Model';
		}

		// Get model.
		$modelName = $prefix . ucfirst($name);

		if (!class_exists($modelName))
		{
			$modelName = '\\Windwalker\\Model\\Model';
		}

		$model = new $modelName;

		$model->setName($name)
			->setOption('com_' . $this->option);

		return $this->model[$name] = $model;
	}
}
