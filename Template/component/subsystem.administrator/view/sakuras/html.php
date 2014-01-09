<?php

use Joomla\DI\Container;
use Windwalker\Model\Model;
use Windwalker\View\Html\GridView;
use Windwalker\Xul\XulEngine;

/**
 * Class SakurasHtmlView
 *
 * @since 1.0
 */
class FlowerViewSakurasHtml extends GridView
{
	/**
	 * @var  string  Property prefix.
	 */
	protected $prefix = 'flower';

	/**
	 * @var  string  Property option.
	 */
	protected $option = 'com_flower';

	/**
	 * @var  string  Property viewItem.
	 */
	protected $viewItem = 'sakura';

	/**
	 * @var  string  Property viewList.
	 */
	protected $viewList = 'sakuras';

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
		$config['grid'] = array(
			'orderCol'  => $this->viewItem . '.catid, ' . $this->viewItem . '.ordering'
		);

		$this->engine = new XulEngine;

		parent::__construct($model, $container, $config, $paths);
	}

	/**
	 * render
	 *
	 * @return void
	 */
	protected function prepareData()
	{
	}

	/**
	 * configToolbar
	 *
	 * @param array $buttonSet
	 * @param null  $canDo
	 *
	 * @return  array
	 */
	protected function configToolbar($buttonSet = array(), $canDo = null)
	{
		$buttonSet = parent::configureToolbar($buttonSet, $canDo);

		return $buttonSet;
	}
}
