<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\View;

use Windwalker\Data\Data;
use Windwalker\Renderer\AbstractRenderer;
use Windwalker\Renderer\PhpRenderer;
use Windwalker\Renderer\RendererInterface;

/**
 * The HtmlView class.
 * 
 * @since  2.0
 */
class HtmlView extends SimpleHtmlView
{
	/**
	 * Property data.
	 *
	 * @var  Data
	 */
	protected $data = null;

	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = 'default';

	/**
	 * Property renderer.
	 *
	 * @var  RendererInterface
	 */
	protected $renderer = null;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   array             $data     The data array.
	 * @param   RendererInterface $renderer The renderer engine.
	 */
	public function __construct($data = array(), RendererInterface $renderer = null)
	{
		$this->renderer = $renderer ? : new PhpRenderer;

		parent::__construct($data);

		$this->data = new Data($this->data);
	}

	/**
	 * getData
	 *
	 * @return  \Windwalker\Data\Data
	 */
	public function getData()
	{
		if (!$this->data)
		{
			$this->data = new Data;
		}

		return $this->data;
	}

	/**
	 * setData
	 *
	 * @param   Data $data
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * render
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	public function render()
	{
		$data = $this->getData();

		$this->prepare($data);

		return $this->renderer->render($this->getLayout(), $data);
	}

	/**
	 * prepareData
	 *
	 * @param   Data $data
	 *
	 * @return  void
	 */
	protected function prepare($data)
	{
	}

	/**
	 * Method to get property Renderer
	 *
	 * @return  RendererInterface|AbstractRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Method to set property renderer
	 *
	 * @param   RendererInterface $renderer
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}

	/**
	 * addPath
	 *
	 * @param string $path
	 * @param int    $priority
	 *
	 * @return  static
	 */
	public function addPath($path, $priority = 100)
	{
		$renderer = $this->getRenderer();

		if ($renderer instanceof AbstractRenderer)
		{
			$renderer->addPath($path, $priority);
		}

		return $this;
	}

	/**
	 * addPaths
	 *
	 * @param array|\Traversable $paths
	 * @param int                $priority
	 *
	 * @return  static
	 */
	public function addPaths($paths, $priority = 100)
	{
		foreach ($paths as $path)
		{
			$this->addPath($path, $priority);
		}

		return $this;
	}
}
