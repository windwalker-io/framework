<?php

use Joomla\DI\Container;
use Windwalker\Model\Model;
use Windwalker\View\Engine\PhpEngine;
use Windwalker\View\Html\FormView;
use Windwalker\Xul\XulEngine;

/**
 * Class SakurasHtmlView
 *
 * @since 1.0
 */
class FlowerViewSakuraHtml extends FormView
{
	/**
	 * Method to instantiate the view.
	 *
	 * @param Model            $model     The model object.
	 * @param Container        $container DI Container.
	 * @param array            $config    View config.
	 * @param SplPriorityQueue $paths     Paths queue.
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array(), \SplPriorityQueue $paths = null)
	{
		$this->engine = new PhpEngine;

		parent::__construct($model, $container, $config, $paths);
	}

	protected function prepareRender()
	{
		parent::prepareRender();
	}
}
