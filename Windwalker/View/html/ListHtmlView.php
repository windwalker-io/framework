<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\Data\Data;
use Windwalker\Model\Model;
use Windwalker\DI\Container;

/**
 * Class ListHtmlView
 *
 * @since 1.0
 */
class ListHtmlView extends HtmlView
{
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
		parent::__construct($model, $container, $config, $paths);

		// Guess the item view as the context.
		if (empty($this->viewList))
		{
			$this->viewList = $this->getName();
		}

		// Guess the list view as the plural of the item view.
		if (empty($this->viewItem))
		{
			$inflector = \JStringInflector::getInstance();

			$this->viewItem = $inflector->toSingular($this->viewList);
		}
	}

	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		parent::prepareRender();

		$data             = $this->getData();
		$data->items      = $this->get('Items');
		$data->pagination = $this->get('Pagination');
		$data->state      = $this->get('State');

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}
	}
}
