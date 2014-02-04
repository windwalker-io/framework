<?php

use Joomla\DI\Container;
use Windwalker\Model\Model;
use Windwalker\View\Engine\PhpEngine;
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
	 * Property prefix.
	 *
	 * @var  string
	 */
	protected $prefix = 'flower';

	/**
	 * Property option.
	 *
	 * @var  string
	 */
	protected $option = 'com_flower';

	/**
	 * Property textPrefix.
	 *
	 * @var string
	 */
	protected $textPrefix = 'COM_FLOWER';

	/**
	 * Property viewItem.
	 *
	 * @var  string
	 */
	protected $viewItem = 'sakura';

	/**
	 * Property viewList.
	 *
	 * @var  string
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

		$this->engine = new PhpEngine;

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
	protected function configureToolbar($buttonSet = array(), $canDo = null)
	{
		$buttonSet = parent::configureToolbar($buttonSet, $canDo);

		if (JDEBUG)
		{
			$buttonSet['trash']['access']  = false;
			$buttonSet['delete']['access'] = true;
		}

		return $buttonSet;
	}
}
