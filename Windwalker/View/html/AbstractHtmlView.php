<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\DI\Container;
use Windwalker\Model\Model;
use Windwalker\View\AbstractView;
use Windwalker\View\Engine\EngineInterface;
use Windwalker\View\Engine\PhpEngine;

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Class AbstractHtmlView
 *
 * @since 1.0
 */
abstract class AbstractHtmlView extends AbstractView
{
	/**
	 * The view layout.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $layout = 'default';

	/**
	 * The paths queue.
	 *
	 * @var    \SplPriorityQueue
	 * @since  12.1
	 */
	protected $paths = null;

	/**
	 * @var  string  Property viewList.
	 */
	protected $viewList = null;

	/**
	 * @var  string  Property viewItem.
	 */
	protected $viewItem = null;

	/**
	 * @var  EngineInterface  Property engine.
	 */
	protected $engine = null;

	/**
	 * Method to instantiate the view.
	 *
	 * @param Model             $model     The model object.
	 * @param Container         $container DI Container.
	 * @param array             $config    View config.
	 * @param \SplPriorityQueue $paths     Paths queue.
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array(), \SplPriorityQueue $paths = null)
	{
		if (!empty($config['engine']) && $config['engine'] instanceof EngineInterface)
		{
			$this->engine = $config['engine'];
		}

		parent::__construct($model, $container, $config);

		// Setup dependencies.
		$this->paths = $paths ? : $this->loadPaths();
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
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * flash
	 *
	 * @param string $msgs
	 * @param string $type
	 *
	 * @return $this
	 */
	public function flash($msgs, $type = 'message')
	{
		$app  = $this->getContainer()->get('app');
		$msgs = (array) $msgs;

		foreach ($msgs as $msg)
		{
			$app->enqueueMessage($msg, $type);
		}

		return $this;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return  \SplPriorityQueue  The paths queue.
	 *
	 * @since   12.1
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * doRedner
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	protected function doRender()
	{
		$engine = $this->getEngine();

		$engine->setPaths($this->paths)
			->setContainer($this->container);

		return $engine->render($this->layout, $this->data);
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  HtmlView  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * getLayout
	 *
	 * @return  string
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param   \SplPriorityQueue  $paths  The paths queue.
	 *
	 * @return  HtmlView  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setPaths(\SplPriorityQueue $paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * Method to load the paths queue.
	 *
	 * @return  \SplPriorityQueue  The paths queue.
	 *
	 * @since   12.1
	 */
	protected function loadPaths()
	{
		return new \SplPriorityQueue;
	}

	/**
	 * getEngine
	 *
	 * @return  EngineInterface
	 */
	public function getEngine()
	{
		if (!($this->engine instanceof EngineInterface))
		{
			$this->engine = new PhpEngine;
		}

		return $this->engine;
	}

	/**
	 * setEngine
	 *
	 * @param   EngineInterface $engine
	 *
	 * @return  AbstractHtmlView  Return self to support chaining.
	 */
	public function setEngine(EngineInterface $engine)
	{
		$this->engine = $engine;

		return $this;
	}
}
