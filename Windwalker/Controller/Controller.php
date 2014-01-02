<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller;

use Whoops\Handler\PrettyPageHandler;
use Windwalker\Controller\Helper\ControllerHelper;
use Windwalker\Debugger\Debugger;
use Windwalker\DI\Container;
use Joomla\DI\Container as JoomlaContainer;
use Joomla\DI\ContainerAwareInterface;
use Windwalker\Model\Model;

/**
 * Class Controller
 *
 * @since 2.0
 */
abstract class Controller extends \JControllerBase implements ContainerAwareInterface
{
	/**
	 * The application object.
	 *
	 * @var    \JApplicationCms
	 * @since  12.1
	 */
	protected $app = null;

	/**
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	protected $prefix = null;

	/**
	 * Property option.
	 *
	 * @var string
	 */
	protected $option = null;

	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * Property componentPath.
	 *
	 * @var string
	 */
	protected $componentPath = null;

	/**
	 * Property reflection.
	 *
	 * @var \ReflectionClass
	 */
	protected $reflection = null;

	/**
	 * Property task.
	 *
	 * @var string
	 */
	protected $task = '';

	/**
	 * Property container.
	 *
	 * @var JoomlaContainer
	 */
	protected $container;

	/**
	 * Instantiate the controller.
	 *
	 * @param   \JInput            $input  The input object.
	 * @param   \JApplicationCms   $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(\JInput $input = null, \JApplicationCms $app = null, $config = array())
	{
		if (!$this->prefix && !empty($config['prefix']))
		{
			$this->prefix = $config['prefix'];
		}

		if (!$this->option && !empty($config['option']))
		{
			$this->option = $config['option'];
		}

		if (!$this->name && !empty($config['name']))
		{
			$this->name = $config['name'];
		}

		if (!$this->task && !empty($config['task']))
		{
			$this->task = $config['task'];
		}

		parent::__construct($input, $app);
	}

	/**
	 * prepareExecute
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
	}

	/**
	 * execute
	 *
	 * @return mixed
	 */
	public function execute()
	{
		$this->prepareExecute();

		$return = $this->doExecute();

		return $this->postExecute($return);
	}

	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	abstract protected function doExecute();

	/**
	 * postExecute
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function postExecute($data = null)
	{
		return $data;
	}

	/**
	 * Fetch HMVC result.
	 *
	 * @param string        $prefix
	 * @param string        $name
	 * @param \JInput|array $input
	 *
	 * @return mixed
	 */
	public function fetch($prefix, $name, $input = null)
	{
		if (!($input instanceof \JInput))
		{
			$newInput = new \JInput($_REQUEST);

			foreach ($input as $field => $value)
			{
				$newInput->set($field, $value);
			}

			$input = $newInput;
		}

		$input->set('task', $name);
		$input->set('hmvc', true);

		$controller = ControllerHelper::getController($prefix, $input, $this->app)
			->setComponentPath($this->componentPath)
			->setContainer($this->container);

		return $controller->execute();
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
	 * @return string
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @param string $task
	 */
	public function setTask($task)
	{
		$this->task = $task;

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
		\JSession::checkToken() or jexit(\JText::_('JInvalid_Token'));
	}

	/**
	 * getModel
	 *
	 * @param null  $name
	 * @param null  $prefix
	 * @param array $config
	 *
	 * @return Model|null
	 */
	public function getModel($name = null, $prefix = null, $config = array())
	{
		// Get name.
		if (!$name)
		{
			$name = $this->getName();
		}

		// Get Prefix
		if (!$prefix)
		{
			$prefix = ucfirst($this->getPrefix());
		}

		$modelName = $prefix . 'Model' . ucfirst($name);

		if (!class_exists($modelName))
		{
			$modelName = '\\Windwalker\\Model\\Model';
		}

		// Get model.
		$container = $this->getContainer();

		try
		{
			$model = $container->get('model.' . $name);

			if ($model instanceof \JModel)
			{
				return $model;
			}
		}
		catch (\InvalidArgumentException $e)
		{
			$model = $container->alias('model.' . $name, $modelName)
				->buildSharedObject($modelName);
		}

		$model->setName($name)
			->setOption($this->option)
			->setContainer($container);

		return $model;
	}

	/**
	 * Get the DI container.
	 *
	 * @return  JoomlaContainer
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException May be thrown if the container has not been set.
	 */
	public function getContainer()
	{
		if (!$this->container)
		{
			$this->container = Container::getInstance($this->option);
		}

		return $this->container;
	}

	/**
	 * Set the DI container.
	 *
	 * @param   JoomlaContainer $container The DI container.
	 *
	 * @return $this
	 *
	 * @since   1.0
	 */
	public function setContainer(JoomlaContainer $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * setMessage
	 *
	 * @param string $msg
	 * @param string $type
	 *
	 * @return Controller
	 */
	public function setMessage($msg, $type = 'message')
	{
		if (!$this->input->get('quiet', false))
		{
			$this->app->enqueueMessage($msg, $type);
		}

		return $this;
	}
}
