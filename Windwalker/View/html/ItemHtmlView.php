<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\Model\Model;
use Windwalker\DI\Container;

/**
 * Class ListHtmlView
 *
 * @since 1.0
 */
class ItemHtmlView extends HtmlView
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
		if (empty($this->viewItem))
		{
			$this->viewItem = $this->getName();
		}

		// Guess the list view as the plural of the item view.
		if (empty($this->viewList))
		{
			$inflector = \JStringInflector::getInstance();

			$this->viewList = $inflector->toPlural($this->viewItem);
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

		$data        = $this->getData();
		$data->item  = $this->get('Item');
		$data->state = $this->get('State');

		if ($errors = $data->state->get('errors'))
		{
			$this->flash($errors);
		}
	}
}
