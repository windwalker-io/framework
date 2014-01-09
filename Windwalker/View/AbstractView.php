<?php

namespace Windwalker\View;

use Joomla\DI\Container as JoomlaContainer;
use Joomla\DI\ContainerAwareInterface;
use Windwalker\Data\Data;
use Windwalker\DI\Container;
use Windwalker\Model\Model;

/**
 * Class View
 *
 * @since 1.0
 */
abstract class AbstractView implements \JView, ContainerAwareInterface
{
	/**
	 * The model object.
	 *
	 * @var    array
	 */
	protected $model = array();

	/**
	 * Property defaultModel.
	 *
	 * @var string
	 */
	protected $defaultModel;

	/**
	 * Property data.
	 *
	 * @var \JData
	 */
	protected $data;

	/**
	 * Property container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * @var  string  Property prefix.
	 */
	protected $prefix;

	/**
	 * @var  string  Property textPrefix.
	 */
	protected $textPrefix;

	/**
	 * Property option.
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * @var  string  Property name.
	 */
	protected $name;

	/**
	 * Method to instantiate the view.
	 *
	 * @param Model      $model     The model object.
	 * @param Container  $container DI Container.
	 * @param array      $config    View config.
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array())
	{
		// Setup dependencies.
		if ($model)
		{
			$modelName = $model->getName();

			$this->defaultModel = strtolower($modelName);

			$this->model[strtolower($modelName)] = $model;
		}

		// Prepare data
		if (!$this->data)
		{
			$this->data = \JArrayHelper::getValue($config, 'data', new Data);
		}

		// Prepare prefix
		if (!$this->prefix)
		{
			$this->prefix = \JArrayHelper::getValue($config, 'prefix', $this->getPrefix());
		}

		// Prepare option
		if (!$this->option)
		{
			$this->option = \JArrayHelper::getValue($config, 'option', 'com_' . $this->prefix);
		}

		// Prepare name
		if (!$this->name)
		{
			$this->name = \JArrayHelper::getValue($config, 'name', $this->getName());
		}

		// Prepare textPrefix
		if (!$this->textPrefix)
		{
			$this->textPrefix = \JArrayHelper::getValue($config, 'text_prefix', $this->option);
		}

		$this->textPrefix = strtoupper($this->textPrefix);

		$this->container = $container ? : Container::getInstance($this->option);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     JView::escape()
	 * @since   12.1
	 */
	public function escape($output)
	{
		return $output;
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   12.1
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		$this->prepareRender();

		$this->prepareData();

		$output = $this->doRender();

		return $this->postRender($output);
	}

	/**
	 * doRedner
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	abstract protected function doRender();

	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
	}

	/**
	 * prepareData
	 *
	 * @return  void
	 */
	protected function prepareData()
	{
	}

	/**
	 * postRender
	 *
	 * @param string $output
	 *
	 * @return  mixed
	 */
	protected function postRender($output)
	{
		return $output;
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string  $name  The name of the model (optional)
	 *
	 * @return  mixed  JModelLegacy object
	 *
	 * @since   12.2
	 */
	public function getModel($name = null)
	{
		if (!$name)
		{
			$name = $this->defaultModel;
		}

		$name = strtolower($name);

		if (empty($this->model[$name]))
		{
			return null;
		}

		return $this->model[$name];
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @param   \JModel $model    The model to add to the view.
	 * @param   boolean $default  Is this the default model?
	 *
	 * @return  object   The added model.
	 */
	public function setModel($model, $default = false)
	{
		$name = strtolower($model->getName());
		$this->model[$name] = $model;

		if ($default)
		{
			$this->defaultModel = $name;
		}

		return $model;
	}

	/**
	 * Method to get data from a registered model or a property of the view.
	 *
	 * @param   string  $cmd    The name of the method to call on the model or the property to get
	 * @param   string  $model  The name of the model to reference or the default value [optional]
	 * @param   array   $args   The arguments to send to model methods.
	 *
	 * @return  mixed  The return value of the method
	 */
	public function get($cmd, $model = null, $args = array())
	{
		// If $model is null we use the default model
		$model = $this->getModel($model);

		// First check to make sure the model requested exists
		if (!$model)
		{
			return null;
		}

		// Model exists, let's build the method name
		$method = 'get' . ucfirst($cmd);

		// Does the method exist?
		if (!method_exists($model, $method))
		{
			// $method = 'load' . ucfirst($cmd);

			return null;
		}

		// The method exists, let's call it and return what we get
		$result = call_user_func_array(array($model, $method), $args);

		return $result;
	}

	/**
	 * getData
	 *
	 * @return \JData
	 */
	public function getData()
	{
		if (!$this->data)
		{
			$this->data = new \JData;
		}

		return $this->data;
	}

	/**
	 * setData
	 *
	 * @param $data
	 *
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Get the DI container.
	 *
	 * @return  Container
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
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setContainer(JoomlaContainer $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOption()
	{
		return $this->option;
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
	 * @return  string
	 */
	public function getPrefix()
	{
		if (!$this->prefix)
		{
			$r = null;

			if (!preg_match('/(.*)View/i', get_class($this), $r))
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$this->prefix = strtolower($r[1]);
		}

		return $this->prefix;
	}

	/**
	 * @param   string $prefix
	 *
	 * @return  AbstractView  Return self to support chaining.
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.2
	 * @throws  \Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$lastPart  = substr($classname, $viewpos + 4);
			$pathParts = explode(' ', \JStringNormalise::fromCamelCase($lastPart));

			if (!empty($pathParts[1]))
			{
				$this->name = strtolower($pathParts[0]);
			}
			else
			{
				$this->name = strtolower($lastPart);
			}
		}

		return $this->name;
	}

	/**
	 * getName
	 *
	 * @param   string $name
	 *
	 * @return  AbstractView  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}
}
