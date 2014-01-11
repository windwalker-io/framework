<?php

namespace Flower\Component;

use Windwalker\Component\Component;
use Windwalker\Helper\LanguageHelper;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
abstract class FlowerComponent extends Component
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = 'Flower';

	/**
	 * prepare
	 *
	 * @return  void
	 */
	protected function prepare()
	{
		// Load language
		$lang = $this->container->get('language');

		LanguageHelper::loadAll($lang->getTag(), $this->option);

		parent::prepare();
	}
}
